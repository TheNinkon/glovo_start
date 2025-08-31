<?php

namespace App\Services;

use App\Models\Forecast;
use App\Models\ForecastSlot;
use Carbon\Carbon;

class ForecastImportService
{
    /**
     * Import forecast slots from a CSV/TSV exported from Excel.
     * Expects a header row with day columns (Mon..Sun or Lun..Dom) and first column as time label.
     */
    public function import(string $filePath, Carbon $weekStart, ?string $name, Carbon $selectionDeadlineAt, string $status = 'published', ?string $cityCode = null): Forecast
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \InvalidArgumentException('Could not open uploaded file.');
        }

        // Detect delimiter from first line
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \InvalidArgumentException('Empty file.');
        }
        $delimiter = $this->detectDelimiter($firstLine);
        // Rewind and parse header
        rewind($handle);
        $header = $this->getCsvRow($handle, $delimiter);
        if (!$header) {
            fclose($handle);
            throw new \InvalidArgumentException('Missing header row.');
        }

        [$timeCol, $dayCols] = $this->mapHeader($header);
        if ($timeCol === null || empty($dayCols)) {
            fclose($handle);
            throw new \InvalidArgumentException('Could not detect time/day columns. Ensure the file has a first column with times and columns Mon..Sun.');
        }

        // Create forecast
        $weekStart = $weekStart->copy()->startOfWeek();
        $forecast = Forecast::create([
            'name' => $name ?: 'Week ' . $weekStart->format('W (Y)'),
            'week_start' => $weekStart,
            'week_end' => $weekStart->copy()->endOfWeek(),
            'selection_deadline_at' => $selectionDeadlineAt,
            'status' => $status,
            'city_code' => $cityCode,
        ]);

        $now = Carbon::now();
        $rows = [];
        // Skip potential second header row if repeated
        while (($row = $this->getCsvRow($handle, $delimiter)) !== null) {
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue; // skip empty lines
            }

            $timeStr = trim((string)($row[$timeCol] ?? ''));
            if ($timeStr === '' || !$this->isTimeLike($timeStr)) {
                // If it's another header, skip
                continue;
            }
            [$startTime, $endTime] = $this->computeTimes($timeStr);

            foreach ($dayCols as $dayIndex => $colIndex) {
                $raw = $row[$colIndex] ?? '';
                $capacity = is_numeric($raw) ? (int)$raw : 0;
                if ($capacity <= 0) continue;
                $date = $weekStart->copy()->addDays($dayIndex)->toDateString();
                $rows[] = [
                    'forecast_id' => $forecast->id,
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'capacity' => $capacity,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        fclose($handle);

        if (!empty($rows)) {
            // Insert in chunks to avoid large single insert
            foreach (array_chunk($rows, 1000) as $chunk) {
                ForecastSlot::insert($chunk);
            }
        }

        return $forecast;
    }

    private function detectDelimiter(string $line): string
    {
        if (str_contains($line, "\t")) return "\t";
        if (str_contains($line, ';')) return ';';
        return ',';
    }

    private function getCsvRow($handle, string $delimiter): ?array
    {
        $row = fgetcsv($handle, 0, $delimiter);
        if ($row === false) return null;
        // Normalize encoding & trim
        return array_map(function ($v) {
            if ($v === null) return '';
            $v = is_string($v) ? $v : (string)$v;
            return trim($v);
        }, $row);
    }

    private function mapHeader(array $header): array
    {
        // Map possible day names to index 0..6 (Mon..Sun)
        $dayMap = [
            'MON' => 0, 'MONDAY' => 0, 'LUN' => 0, 'LUNES' => 0,
            'TUE' => 1, 'TUESDAY' => 1, 'MAR' => 1, 'MARTES' => 1,
            'WED' => 2, 'WEDNESDAY' => 2, 'MIE' => 2, 'MIÉ' => 2, 'MIERCOLES' => 2, 'MIÉRCOLES' => 2,
            'THU' => 3, 'THURSDAY' => 3, 'JUE' => 3, 'JUEVES' => 3,
            'FRI' => 4, 'FRIDAY' => 4, 'VIE' => 4, 'VIERNES' => 4,
            'SAT' => 5, 'SATURDAY' => 5, 'SAB' => 5, 'SÁB' => 5, 'SABADO' => 5, 'SÁBADO' => 5,
            'SUN' => 6, 'SUNDAY' => 6, 'DOM' => 6, 'DOMINGO' => 6,
        ];

        $timeCol = null;
        $dayCols = [];
        foreach ($header as $idx => $label) {
            $norm = strtoupper(trim(preg_replace('/\s+/', '', $label)));
            if ($timeCol === null && ($norm === 'ETIQUETASDEFILA' || $norm === 'ETIQUETASDEFILA:' || $norm === 'TIME' || $norm === 'HORA')) {
                $timeCol = $idx;
                continue;
            }
            if ($norm === 'TOTALGENERAL' || $norm === 'GRANDTOTAL' || $norm === 'TOTAL') {
                continue; // ignore total column
            }
            if (isset($dayMap[$norm])) {
                $dayCols[$dayMap[$norm]] = $idx;
            }
        }

        // If time column not explicitly labeled, assume first column
        if ($timeCol === null) $timeCol = 0;

        // Ensure dayCols are ordered by day index
        ksort($dayCols);

        return [$timeCol, $dayCols];
    }

    private function isTimeLike(string $value): bool
    {
        $v = trim($value);
        // Accept H:i or H:i:s
        return (bool) preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $v);
    }

    private function computeTimes(string $timeStr): array
    {
        $t = trim($timeStr);
        // Ensure we have H:i:s
        if (preg_match('/^\d{1,2}:\d{2}$/', $t)) {
            $t .= ':00';
        }
        // Pad hour to two digits
        $parts = explode(':', $t);
        $h = str_pad($parts[0] ?? '0', 2, '0', STR_PAD_LEFT);
        $m = str_pad($parts[1] ?? '00', 2, '0', STR_PAD_LEFT);
        $s = str_pad($parts[2] ?? '00', 2, '0', STR_PAD_LEFT);
        $norm = "$h:$m:$s";
        $start = Carbon::createFromFormat('H:i:s', $norm)->format('H:i:s');
        $end = Carbon::createFromFormat('H:i:s', $norm)->addMinutes(30)->format('H:i:s');
        return [$start, $end];
    }

    /**
     * Replace all slots for an existing forecast using the file contents.
     */
    public function replaceSlots(Forecast $forecast, string $filePath): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \InvalidArgumentException('Could not open uploaded file.');
        }
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            throw new \InvalidArgumentException('Empty file.');
        }
        $delimiter = $this->detectDelimiter($firstLine);
        rewind($handle);
        $header = $this->getCsvRow($handle, $delimiter);
        if (!$header) {
            fclose($handle);
            throw new \InvalidArgumentException('Missing header row.');
        }

        [$timeCol, $dayCols] = $this->mapHeader($header);
        if ($timeCol === null || empty($dayCols)) {
            fclose($handle);
            throw new \InvalidArgumentException('Could not detect time/day columns.');
        }

        $weekStart = Carbon::parse($forecast->week_start)->startOfWeek();

        // Wipe existing slots
        \App\Models\ForecastSlot::where('forecast_id', $forecast->id)->delete();

        $now = Carbon::now();
        $rows = [];
        while (($row = $this->getCsvRow($handle, $delimiter)) !== null) {
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;
            $timeStr = trim((string)($row[$timeCol] ?? ''));
            if ($timeStr === '' || !$this->isTimeLike($timeStr)) continue;
            [$startTime, $endTime] = $this->computeTimes($timeStr);
            foreach ($dayCols as $dayIndex => $colIndex) {
                $raw = $row[$colIndex] ?? '';
                $capacity = is_numeric($raw) ? (int)$raw : 0;
                if ($capacity <= 0) continue;
                $date = $weekStart->copy()->addDays($dayIndex)->toDateString();
                $rows[] = [
                    'forecast_id' => $forecast->id,
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'capacity' => $capacity,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        fclose($handle);

        foreach (array_chunk($rows, 1000) as $chunk) {
            \App\Models\ForecastSlot::insert($chunk);
        }
    }
}
