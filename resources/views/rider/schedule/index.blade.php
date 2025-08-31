@extends('layouts/layoutMaster')

@section('title', 'Mi Horario Semanal')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
  <style>
    .schedule-card { display:flex; flex-direction:column; height:calc(100vh - 10rem); overflow:hidden; padding:0 !important }
    .schedule-card .card-body { flex-grow:1; padding:0; display:flex; flex-direction:column; overflow:hidden }
    .schedule-header { flex-shrink:0; display:flex; justify-content:space-between; align-items:center; padding:.75rem 1rem; border-bottom:1px solid #e7e7e7; background:#fff; z-index:11 }
    .schedule-week-nav { font-size:1rem; font-weight:500 }
    .deadline-wrapper { display:flex; align-items:center; gap:.75rem }
    .deadline-label { font-size:.85rem; font-weight:500; color:#6c757d }
    #deadline-display { display:flex; align-items:center; gap:.5rem; font-size:.9rem; padding:.25rem .75rem; border-radius:15px; background-color:#f8f8f8; border:1px solid #e7e7e7; color:#6c757d }
    #deadline-display.expiring #countdown-text { background-color:rgba(255,152,0,.1); color:#ff9800; padding:.1rem .4rem; border-radius:4px }
    #deadline-display.expired #countdown-text { background-color:rgba(244,67,54,.1); color:#f44336; padding:.1rem .4rem; border-radius:4px }
    .sticky-header { position:sticky; top:0; background-color:#fff; z-index:10; flex-shrink:0; box-shadow:0 2px 4px rgba(0,0,0,.05) }
    .date-selector { display:flex; width:100%; list-style:none; padding-left:0; margin-bottom:0; padding:.5rem }
    .date-selector-item { flex:1 1 0; text-align:center; padding:.75rem .5rem; border-radius:50px; cursor:pointer; transition:all .2s ease-in-out }
    .date-selector-item.active { background-color:#f33a58; color:#fff }
    .date-selector-item.active .day-name, .date-selector-item.active .day-number { color:#fff }
    .schedule-tabs { display:flex; justify-content:space-around; border-top:1px solid #e7e7e7 }
    .schedule-tab { padding:1rem; cursor:pointer; font-weight:600; color:#6c757d; border-bottom:3px solid transparent }
    .schedule-tab.active { color:#f33a58; border-bottom-color:#f33a58 }
    .schedule-scroll-area { flex-grow:1; overflow-y:auto; padding:1rem 0 120px 0 }
    .schedule-content { display:none }
    .schedule-content.active { display:block }
    .daily-schedule-slot { display:flex; align-items:stretch; margin:0 1rem 4px 1rem; min-height:50px }
    .daily-schedule-slot .slot-time { flex-basis:70px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-weight:600; color:#555 }
    .daily-schedule-slot .slot-bar { flex-grow:1; border-radius:6px; transition:all .2s ease; padding:.5rem 1rem; display:flex; align-items:center; justify-content:space-between; font-size:1.1rem }
    .slot-bar.available { background-color:#fff; border:1px solid #dcdcdc; cursor:pointer }
    .slot-bar.available:hover { background-color:#f0f2f5 }
    .slot-bar.available .ti-plus { color:#aaa; opacity:0; transition:opacity .2s }
    .slot-bar.available:hover .ti-plus { opacity:1 }
    .slot-bar.mine { background-color:#28a745; color:#fff; font-weight:700; cursor:pointer }
    .slot-bar.locked { background-color:#dee2e6; cursor:not-allowed; color:#6c757d }
    .slot-bar.locked .ti.tabler-lock { color:#aaa }
    .slot-bar .ti.tabler-minus { color:#fff }
    .floating-summary-bar { position:absolute; bottom:0; left:0; right:0; background-color:#fff; box-shadow:0 -2px 10px rgba(0,0,0,.08); padding:1rem; display:flex; justify-content:space-around; align-items:center; z-index:11; border-top:1px solid #e7e7e7 }
    .summary-item { text-align:center }
    .summary-item .value { font-size:1.2rem; font-weight:700 }
    .summary-item .label { font-size:.8rem; color:#6c757d }
    .reserved-slot-item { background-color:#f8f9fa; padding:1rem; border-radius:6px; margin:0 1rem .5rem 1rem; display:flex; justify-content:space-between; align-items:center; font-weight:600 }
    .not-clickable { pointer-events:none; opacity:.6 }
  </style>
@endsection

@section('content')
  <div class="card schedule-card"
    data-api-url="{{ route('rider.schedule.data') }}"
    data-select-url="{{ route('rider.schedule.select') }}"
    data-deselect-url="{{ route('rider.schedule.deselect') }}"
    data-csrf-token="{{ csrf_token() }}"
    data-is-locked="{{ $isLocked ? 'true' : 'false' }}"
    data-forecast-id="{{ $forecast_id ?? 'null' }}"
    data-default-day="{{ $defaultDay }}">

    <div class="schedule-header">
      <a href="{{ $prevWeek ?? '#' }}" class="btn btn-icon rounded-pill {{ !$prevWeek ? 'disabled' : '' }}"><i class="ti tabler-chevron-left"></i></a>
      <div class="schedule-week-nav">{{ $startOfWeek->format('j M') }} - {{ $endOfWeek->format('j M, Y') }}</div>
      @if ($deadline)
        <div class="deadline-wrapper">
          <span class="deadline-label">Plazo de reserva:</span>
          <div id="deadline-display" data-deadline="{{ $deadline->toIso8601String() }}">
            <i class="ti tabler-clock"></i>
            <span id="countdown-text">Calculando...</span>
          </div>
        </div>
      @endif
      <a href="{{ $nextWeek ?? '#' }}" class="btn btn-icon rounded-pill {{ !$nextWeek ? 'disabled' : '' }}"><i class="ti tabler-chevron-right"></i></a>
    </div>

    <div class="card-body">
      <div class="sticky-header">
        <ul class="date-selector">
          @if ($weekDates)
            @foreach ($weekDates as $day)
              <li class="date-selector-item @if ($day['full'] === $defaultDay) active @endif" data-date="{{ $day['full'] }}">
                <div class="day-name">{{ $day['dayName'] }}</div>
                <div class="day-number">{{ $day['dayNum'] }}</div>
              </li>
            @endforeach
          @endif
        </ul>
        <div class="schedule-tabs">
          <div class="schedule-tab active" data-tab="disponibles">Horas Disponibles</div>
          <div class="schedule-tab" data-tab="reservadas">Horas Reservadas</div>
        </div>
      </div>

      <div class="schedule-scroll-area">
        <div id="disponibles-content" class="schedule-content active">
          @if (!$scheduleData)
            <div class="alert alert-warning text-center m-4">No hay un horario disponible para esta semana.</div>
          @else
            <div class="text-center p-4">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
          @endif
        </div>
        <div id="reservadas-content" class="schedule-content"></div>
      </div>
    </div>

    <div class="floating-summary-bar">
      <div class="summary-item">
        <div id="summary-contratadas" class="value">{{ $summary['contractedHours'] ?? 0 }}h</div>
        <div class="label">Contratadas</div>
      </div>
      <div class="summary-item">
        <div id="summary-reservadas" class="value">{{ number_format($summary['reservedHours'] ?? 0, 1) }}h</div>
        <div class="label">Reservadas</div>
      </div>
      <div class="summary-item">
        <div id="summary-comodines" class="value">{{ $summary['wildcards'] ?? 0 }}</div>
        <div class="label">Comodines</div>
      </div>
      <div class="summary-item" style="min-width:200px">
        <div class="label">Progreso</div>
        <div class="progress" style="height:8px; width:200px">
          <div id="progress-bar" class="progress-bar" role="progressbar" style="width:0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small id="progress-text" class="text-muted"></small>
      </div>
      <div>
        <button id="confirm-schedule" type="button" class="btn btn-success" disabled>
          <i class="ti tabler-check me-1"></i> Confirmar horarios
        </button>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const scheduleCard = document.querySelector('.schedule-card');
      if (!scheduleCard) return;

      const csrfToken = scheduleCard.dataset.csrfToken;
      const selectUrl = scheduleCard.dataset.selectUrl;
      const deselectUrl = scheduleCard.dataset.deselectUrl;
      const defaultDay = scheduleCard.dataset.defaultDay;
      let activeDay = defaultDay;
      const apiUrl = scheduleCard.dataset.apiUrl;

      let scheduleIsLocked = scheduleCard.dataset.isLocked === 'true';
      let contractedHours = 0;
      let reservedHours = 0;
      let forecastId = scheduleCard.dataset.forecastId === 'null' ? null : scheduleCard.dataset.forecastId;

      const hoursReservedEl = document.getElementById('summary-reservadas');
      const hoursContractedEl = document.getElementById('summary-contratadas');
      const editsRemainingEl = document.getElementById('summary-comodines');
      const dateSelectorItems = document.querySelectorAll('.date-selector-item');
      const availableContent = document.getElementById('disponibles-content');
      const reservedContent = document.getElementById('reservadas-content');
      const scheduleTabs = document.querySelectorAll('.schedule-tab');

      function startCountdown(deadlineDate) {
        const deadline = new Date(deadlineDate);
        const countdownEl = document.getElementById('countdown-text');
        const deadlineDisplay = document.getElementById('deadline-display');
        const update = () => {
          const now = Date.now();
          const distance = deadline - now;
          if (distance < 0) { countdownEl.textContent = '¡Plazo expirado!'; deadlineDisplay.classList.add('expired'); scheduleIsLocked = true; return; }
          const days = Math.floor(distance / 86400000);
          const hours = Math.floor((distance % 86400000) / 3600000);
          const minutes = Math.floor((distance % 3600000) / 60000);
          const seconds = Math.floor((distance % 60000) / 1000);
          countdownEl.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
          if (distance < 86400000) deadlineDisplay.classList.add('expiring');
          requestAnimationFrame(update);
        };
        update();
      }

      async function loadSchedule(dateString) {
        availableContent.innerHTML = `<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>`;
        reservedContent.innerHTML = `<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>`;
        const url = new URL(apiUrl, window.location.origin);
        url.searchParams.set('week', dateString);
        try {
          const response = await fetch(url.toString());
          const data = await response.json();
          if (data.redirect_week) {
            // Fallback automático a la semana sugerida
            return loadSchedule(data.redirect_week);
          }
          if (!response.ok || !data.success) {
            availableContent.innerHTML = `<div class="alert alert-warning text-center m-4">${data.message || 'No hay un horario disponible para esta semana.'}</div>`;
            reservedContent.innerHTML = '';
            return;
          }
          forecastId = data.forecast_id;
          scheduleIsLocked = data.isLocked;
          contractedHours = data.contractedHours || 0;
          reservedHours = data.reservedHours || 0;
          hoursContractedEl.textContent = `${contractedHours}h`;
          hoursReservedEl.textContent = `${reservedHours.toFixed(1)}h`;
          editsRemainingEl.textContent = `${data.wildcards || 0}`;
          if (data.scheduleData) renderSchedule(data.scheduleData); else availableContent.innerHTML = `<div class="alert alert-warning text-center m-4">No hay un horario disponible para esta semana.</div>`;
          const ddEl = document.getElementById('deadline-display');
          if (data.deadline) { if (ddEl) { ddEl.dataset.deadline = data.deadline; startCountdown(data.deadline); } }
          else { if (ddEl) { ddEl.innerHTML = '<span class="deadline-label">No hay plazo.</span>'; } }
        } catch (e) {
          console.error(e);
          availableContent.innerHTML = `<div class="alert alert-danger text-center m-4">Error al cargar el horario.</div>`;
          reservedContent.innerHTML = '';
        }
      }

      function renderSchedule(scheduleData) {
        availableContent.innerHTML = '';
        reservedContent.innerHTML = '';
        if (!Array.isArray(scheduleData)) { availableContent.innerHTML = `<div class=\"alert alert-warning text-center m-4\">No hay datos de horario para mostrar.</div>`; return; }
        scheduleData.forEach(dayData => {
          const sectionId = 'day-' + dayData.date;
          const header = document.createElement('h6');
          header.className = 'text-center text-muted text-uppercase mt-4';
          header.textContent = dayData.dayName;
          const dayAvail = document.createElement('div');
          dayAvail.className = 'day-section';
          dayAvail.id = sectionId;
          const dayRes = document.createElement('div');
          dayRes.className = 'day-section';
          dayRes.id = sectionId + '-res';
          dayAvail.appendChild(header.cloneNode(true));
          dayRes.appendChild(header.cloneNode(true));
          (dayData.slots || []).forEach(slot => {
            const isMine = slot.status === 'mine';
            const isAvailable = slot.status === 'available';
            const isLockedOrOver = scheduleIsLocked || (!isMine && contractedHours && reservedHours >= contractedHours);
            const classList = ['slot-bar'];
            let iconHtml = '';
            let cursor = '';
            if (isMine) { classList.push('mine'); iconHtml = `<i class="ti tabler-minus"></i>`; cursor = 'pointer'; }
            else if (isAvailable) { classList.push('available'); iconHtml = `<i class="ti tabler-plus"></i>`; cursor = 'pointer'; }
            else { classList.push('locked'); iconHtml = `<i class="ti tabler-lock"></i>`; cursor = 'not-allowed'; }
            if (isLockedOrOver && !isMine) { cursor = 'not-allowed'; if (!classList.includes('locked')) classList.push('locked'); }
            const slotEl = document.createElement('div');
            slotEl.className = 'daily-schedule-slot';
            // Ocultamos la demanda
            slotEl.innerHTML = `<div class=\"slot-time\">${slot.time}</div><div class=\"${classList.join(' ')}\" style=\"cursor:${cursor}\" data-slot-identifier=\"${slot.identifier}\"><span>${slot.time}</span>${iconHtml}</div>`;
        if (isMine) dayRes.appendChild(slotEl); else dayAvail.appendChild(slotEl);
          });
          availableContent.appendChild(dayAvail);
          reservedContent.appendChild(dayRes);
        });
        attachListeners();

        // Scroll al día activo en el contenedor visible
        const root = document.querySelector('.schedule-scroll-area');
        const target = document.getElementById('day-' + activeDay);
        if (target && root) { root.scrollTo({ top: target.offsetTop - root.offsetTop, behavior: 'smooth' }); }

        // Configura observer según pestaña activa
        setupObserver(getActiveTab());
      }

      function attachListeners() {
        document.querySelectorAll('.slot-bar').forEach(slot => {
          if (slot.classList.contains('available') && !scheduleIsLocked) slot.addEventListener('click', handleSelectSlot);
          if (slot.classList.contains('mine') && !scheduleIsLocked) slot.addEventListener('click', handleDeselectSlot);
        });
      }

      let ioInstance = null;
      function getActiveTab() {
        const active = document.querySelector('.schedule-tab.active');
        return active ? active.dataset.tab : 'disponibles';
      }
      function setupObserver(tab) {
        if (ioInstance) { ioInstance.disconnect(); ioInstance = null; }
        const root = document.querySelector('.schedule-scroll-area');
        ioInstance = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const date = entry.target.id.replace('day-','').replace('-res','');
              document.querySelectorAll('.date-selector-item').forEach(i => i.classList.remove('active'));
              const li = document.querySelector(`.date-selector-item[data-date="${date}"]`);
              if (li) li.classList.add('active');
              activeDay = date;
            }
          });
        }, { root, threshold: 0.6 });
        const selector = tab === 'reservadas' ? '.day-section[id$="-res"]' : '.day-section:not([id$="-res"])';
        document.querySelectorAll(selector).forEach(sec => ioInstance.observe(sec));
      }

      async function handleSelectSlot(e) {
        const slotEl = e.currentTarget; const slotId = slotEl.dataset.slotIdentifier;
        try {
          const res = await fetch(selectUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ slot: slotId, forecast_id: forecastId }) });
          const data = await res.json();
          if (!res.ok) return Swal.fire({ icon:'error', title:'Error', text: data.message || 'Ha ocurrido un error.' });
          reservedHours = (reservedHours || 0) + 0.5; hoursReservedEl.textContent = `${reservedHours.toFixed(1)}h`;
          await Swal.fire({ icon:'success', title:'Éxito', text: data.message, timer:1200, showConfirmButton:false });
          loadSchedule(activeDay).then(refreshProgress);
        } catch (_) { Swal.fire({ icon:'error', title:'Error de Conexión', text:'No se pudo conectar con el servidor.' }); }
      }

      async function handleDeselectSlot(e) {
        const slotEl = e.currentTarget; const slotId = slotEl.dataset.slotIdentifier;
        try {
          const res = await fetch(deselectUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify({ slot: slotId }) });
          const data = await res.json();
          if (!res.ok) return Swal.fire({ icon:'error', title:'Error', text: data.message || 'Ha ocurrido un error.' });
          reservedHours = Math.max(0, (reservedHours || 0) - 0.5); hoursReservedEl.textContent = `${reservedHours.toFixed(1)}h`;
          await Swal.fire({ icon:'success', title:'Éxito', text: data.message, timer:1200, showConfirmButton:false });
          loadSchedule(activeDay).then(refreshProgress);
        } catch (_) { Swal.fire({ icon:'error', title:'Error de Conexión', text:'No se pudo conectar con el servidor.' }); }
      }

      dateSelectorItems.forEach(item => item.addEventListener('click', function() {
        const date = this.dataset.date;
        activeDay = date;
        dateSelectorItems.forEach(i => i.classList.remove('active')); this.classList.add('active');
        const root = document.querySelector('.schedule-scroll-area');
        const target = document.getElementById('day-' + date);
        if (target && root) { root.scrollTo({ top: target.offsetTop - root.offsetTop, behavior: 'smooth' }); }
      }));
      scheduleTabs.forEach(tab => tab.addEventListener('click', function() {
        scheduleTabs.forEach(t => t.classList.remove('active')); this.classList.add('active');
        document.querySelectorAll('.schedule-content').forEach(c => c.classList.remove('active'));
        document.getElementById(this.dataset.tab + '-content').classList.add('active');
        setupObserver(getActiveTab());
      }));

      async function refreshProgress() {
        if (!forecastId) return;
        try {
          const url = new URL('{{ route('rider.schedule.progress') }}', window.location.origin);
          url.searchParams.set('forecast_id', forecastId);
          const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
          const data = await res.json();
          if (!res.ok || !data.success) return;
          const required = data.required_minutes || 0;
          const reserved = data.reserved_minutes || 0;
          const pct = required ? Math.min(100, Math.round((reserved / required) * 100)) : 0;
          const bar = document.getElementById('progress-bar');
          const txt = document.getElementById('progress-text');
          if (bar) { bar.style.width = pct + '%'; bar.setAttribute('aria-valuenow', pct); }
          if (txt) { txt.textContent = `${Math.round(reserved/60)}h / ${Math.round(required/60)}h`; }
          const canConfirm = (required > 0 && reserved === required && !data.locked);
          const btn = document.getElementById('confirm-schedule');
          if (btn) btn.disabled = !canConfirm;
        } catch (_) {}
      }

      const confirmBtn = document.getElementById('confirm-schedule');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
          if (!forecastId) return;
          try {
            const res = await fetch('{{ route('rider.schedule.commit') }}', {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
              body: JSON.stringify({ forecast_id: forecastId })
            });
            const data = await res.json();
            if (!res.ok || data.success === false) {
              return Swal.fire({ icon:'error', title:'Error', text: data.message || 'No se pudo confirmar.' });
            }
            await Swal.fire({ icon:'success', title:'Confirmado', text:'Horarios confirmados.' });
            loadSchedule(defaultDay).then(refreshProgress);
          } catch (_) {
            Swal.fire({ icon:'error', title:'Error', text:'No se pudo confirmar.' });
          }
        });
      }

      // Initial load
      loadSchedule(defaultDay).then(refreshProgress);
    });
  </script>
@endsection
