<table class="table table-sm align-middle">
  <thead>
    <tr>
      <th>@lang('app.incoming_name')</th>
      <th>@lang('app.language_script')</th>
      <th>@lang('app.normalized')</th>
      <th>@lang('app.top_suggestion')</th>
      <th>@lang('app.decision')</th>
      <th>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-success" id="accept-all-strong">@lang('app.accept_all_strong')</button>
          <button type="button" class="btn btn-outline-secondary" id="reject-all-low">@lang('app.reject_all_low')</button>
        </div>
      </th>
    </tr>
  </thead>
  <tbody>
    @foreach($rows as $i => $row)
      @php
        $incoming = $row['opponent_name'] ?? ($row['opponent'] ?? ($row['opponent_id'] ?? ''));
        $suggest = $opponentSuggestions[$i] ?? null;
        $top = $suggest['top'][0] ?? null;
      @endphp
      <tr data-row="{{ $i }}">
        <td dir="auto">{{ $incoming }}</td>
        <td><span class="badge bg-secondary">{{ $suggest['script'] ?? '-' }}</span></td>
        <td dir="auto">{{ $suggest['normalized'] ?? '-' }}</td>
        <td>
          @if($top)
            <div class="d-flex align-items-center">
              <strong dir="auto">{{ $top['label'] }}</strong>
              <span class="ms-2 badge {{ $top['band']==='strong'?'bg-success':($top['band']==='likely'?'bg-warning text-dark':'bg-secondary') }}"
                    data-bs-toggle="tooltip"
                    title="lev: {{ number_format(($top['breakdown']['lev'] ?? 0)*100,1) }}% | dice: {{ number_format(($top['breakdown']['dice'] ?? 0)*100,1) }}% | jaro: {{ number_format(($top['breakdown']['jaro'] ?? 0)*100,1) }}% @if(isset($top['breakdown']['phon'])) | phon: {{ number_format(($top['breakdown']['phon'] ?? 0)*100,1) }}% @endif \nwhy: {{ $top['why'] ?? '' }}">
                {{ number_format($top['score']*100,1) }}%
              </span>
            </div>
          @else
            <span class="text-muted">—</span>
          @endif
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            @if($top)
              <button type="button" class="btn btn-outline-primary"
                data-action="use-suggestion" data-row="{{ $i }}" data-id="{{ $top['id'] }}">
                @lang('app.use_suggestion')
              </button>
            @endif
            <button type="button" class="btn btn-outline-success"
              data-action="keep-new" data-row="{{ $i }}">
              @lang('app.keep_as_new')
            </button>
            <button type="button" class="btn btn-outline-secondary"
              data-bs-toggle="modal" data-bs-target="#opponentModal" data-row="{{ $i }}">
              @lang('app.more_options')
            </button>
          </div>
        </td>
        <td>
          <input type="hidden" name="decisions[{{ $i }}][type]" value="">
          <input type="hidden" name="decisions[{{ $i }}][opponent_id]" value="">
          <input type="hidden" name="decisions[{{ $i }}][alias]" value="1">
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="modal fade" id="opponentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">@lang('app.choose_opponent')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="store-alias" checked>
          <label class="form-check-label" for="store-alias">@lang('app.also_store_alias')</label>
        </div>
        <div id="opponent-modal-content"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">@lang('app.close')</button>
      </div>
    </div>
  </div>
  </div>

@push('scripts')
<script>
(() => {
  const decisions = {};

  function markRow(row, type) {
    const tr = document.querySelector(`tr[data-row="${row}"]`);
    if (!tr) return;
    tr.classList.remove('table-success','table-warning','table-secondary');
    if (type === 'match') tr.classList.add('table-success');
    if (type === 'new') tr.classList.add('table-warning');
  }

  document.querySelectorAll('[data-action="use-suggestion"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const row = btn.dataset.row, id = btn.dataset.id;
      decisions[row] = { type: 'match', opponent_id: id, alias: 1 };
      document.querySelector(`input[name="decisions[${row}][type]"]`).value = 'match';
      document.querySelector(`input[name="decisions[${row}][opponent_id]"]`).value = id;
      document.querySelector(`input[name="decisions[${row}][alias]"]`).value = 1;
      markRow(row, 'match');
    });
  });

  document.querySelectorAll('[data-action="keep-new"]').forEach(btn => {
    btn.addEventListener('click', () => {
      const row = btn.dataset.row;
      decisions[row] = { type: 'new', opponent_id: '', alias: 1 };
      document.querySelector(`input[name="decisions[${row}][type]"]`).value = 'new';
      document.querySelector(`input[name="decisions[${row}][opponent_id]"]`).value = '';
      document.querySelector(`input[name="decisions[${row}][alias]"]`).value = 1;
      markRow(row, 'new');
    });
  });

  document.getElementById('accept-all-strong')?.addEventListener('click', () => {
    document.querySelectorAll('tr[data-row]').forEach(tr => {
      const row = tr.dataset.row;
      const useBtn = tr.querySelector('[data-action="use-suggestion"]');
      const badge = tr.querySelector('.badge');
      if (useBtn && badge && badge.classList.contains('bg-success')) {
        useBtn.click();
      }
    });
  });

  document.getElementById('reject-all-low')?.addEventListener('click', () => {
    document.querySelectorAll('tr[data-row]').forEach(tr => {
      const row = tr.dataset.row;
      const badge = tr.querySelector('.badge');
      if (!badge || (!badge.classList.contains('bg-success') && !badge.classList.contains('bg-warning'))) {
        const btn = tr.querySelector('[data-action="keep-new"]');
        if (btn) btn.click();
      }
    });
  });

  if (window.bootstrap?.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  }
})();
</script>
@endpush


