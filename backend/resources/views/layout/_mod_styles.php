<?php
/**
 * Estilos compartidos para los módulos nuevos (compras, gastos,
 * contabilidad, RRHH). Se incluye con: require __DIR__ . '/../layout/_mod_styles.php';
 */
?>
<style>
.mod-topbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
.mod-topbar h2 { margin:0 0 4px; color:#172554; font-size:22px; }
.mod-topbar p  { margin:0; color:#6b7280; font-size:14px; }
.btn { display:inline-flex; align-items:center; border:0; border-radius:12px; padding:10px 16px; font-weight:700; text-decoration:none; cursor:pointer; font-size:14px; white-space:nowrap; font-family:inherit; transition:opacity .15s; }
.btn:hover { opacity:.85; }
.btn-primary   { background:#1e3a8a; color:#fff; }
.btn-secondary { background:#e0e7ff; color:#1e3a8a; }
.btn-success   { background:#dcfce7; color:#166534; }
.btn-warning   { background:#fef3c7; color:#92400e; }
.btn-danger    { background:#fee2e2; color:#991b1b; }
.btn-sm { padding:7px 12px; font-size:13px; }
.alert { padding:13px 16px; border-radius:14px; margin-bottom:16px; border:1px solid transparent; font-size:14px; }
.alert-success { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
.alert-error   { background:#fef2f2; color:#991b1b; border-color:#fecaca; }
.card { background:#fff; border:1px solid #dbe3ef; border-radius:20px; box-shadow:0 4px 24px rgba(15,23,42,.08); overflow:hidden; margin-bottom:18px; }
.card-pad { padding:22px; }
.mod-table { width:100%; border-collapse:collapse; }
.mod-table th, .mod-table td { padding:12px 15px; text-align:left; border-bottom:1px solid #e5e7eb; font-size:14px; vertical-align:middle; }
.mod-table th { background:#eff6ff; color:#172554; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
.mod-table tr:last-child td { border-bottom:none; }
.status { display:inline-flex; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:800; }
.st-ok      { background:#dcfce7; color:#166534; }
.st-warn    { background:#fef3c7; color:#92400e; }
.st-bad     { background:#fee2e2; color:#991b1b; }
.st-neutral { background:#e0e7ff; color:#1e3a8a; }
.empty { padding:40px; text-align:center; color:#6b7280; }
.fg { display:flex; flex-direction:column; gap:6px; }
.fg label { font-size:13px; font-weight:700; color:#374151; }
.fg input, .fg select, .fg textarea { border:1px solid #d1d5db; border-radius:10px; padding:10px 12px; font-size:14px; font-family:inherit; }
.grid2 { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
.grid3 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.grid4 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
.info-item .lbl { font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
.info-item .val { font-size:15px; font-weight:700; color:#172554; margin-top:2px; }
.kpi-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; margin-bottom:18px; }
.kpi { background:#fff; border:1px solid #dbe3ef; border-radius:16px; padding:16px 18px; }
.kpi .kpi-lbl { font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
.kpi .kpi-val { font-size:22px; font-weight:800; color:#172554; margin-top:4px; }
.inline-form { display:inline; }

/* ── Tablas responsivas: scroll horizontal en pantallas pequeñas ── */
.card { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.mod-table { min-width:640px; }

@media(max-width:680px){
    .grid2,.grid3,.grid4{ grid-template-columns:1fr; }
    .mod-topbar { flex-direction:column; align-items:stretch; }
    .mod-topbar h2 { font-size:18px; }
    .mod-topbar > div:last-child { display:flex; flex-wrap:wrap; gap:8px; }
    .btn { padding:9px 13px; font-size:13px; }
    .card-pad { padding:14px; }
    .kpi .kpi-val { font-size:18px; }
    .info-grid { grid-template-columns:repeat(2,1fr); }
}
</style>
