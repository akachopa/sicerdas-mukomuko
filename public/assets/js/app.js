// Inisialisasi DataTables server-side dengan konvensi:
// - kolom pertama  : nomor urut (tidak dapat diurutkan)
// - kolom terakhir : aksi (tidak dapat diurutkan) — hanya untuk data master
function initDataTable(selector, url, options = {}) {
  const $table = $(selector);
  if (!$table.length) return null;

  const columnCount = $table.find('thead th').length;
  const columnDefs = [
    { targets: 0, orderable: false, searchable: false, width: '48px', className: 'text-muted' },
  ];
  if (!options.noActionColumn) {
    columnDefs.push({ targets: columnCount - 1, orderable: false, searchable: false, className: 'text-end' });
  }

  return $table.DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: url, type: 'GET' },
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [],
    columnDefs: columnDefs,
    language: window.DT_LANG || {},
    ...options.dt,
  });
}

// POST sederhana dengan token CSRF (untuk aksi toggle/status)
function postAction(url, data = {}) {
  return $.ajax({
    url: url,
    type: 'POST',
    data: data,
    headers: { 'X-CSRF-Token': window.CSRF_TOKEN },
  });
}

$(function () {
  // Konfirmasi umum
  $(document).on('submit', 'form[data-confirm]', function (e) {
    if (!window.confirm($(this).data('confirm'))) e.preventDefault();
  });

  // Format input rupiah ribuan
  $(document).on('input', 'input[data-money]', function () {
    const raw = this.value.replace(/[^\d]/g, '');
    this.value = raw ? Number(raw).toLocaleString('id-ID') : '';
  });
  $(document).on('submit', 'form', function () {
    $(this).find('input[data-money]').each(function () {
      this.value = this.value.replace(/\./g, '').replace(/,/g, '');
    });
  });
});
