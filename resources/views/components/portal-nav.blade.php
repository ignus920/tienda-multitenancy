@props(['active' => 'dashboard'])

@once
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Public+Sans:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
<style>
  /* Modo claro (por defecto). El ERP usa la clase .dark en <body> para modo oscuro. */
  :root{
    --fp-bg:#EDF0F6; --fp-surface:#FFFFFF; --fp-surface-2:#F6F8FC;
    --fp-ink:#0E1626; --fp-ink-2:#54617B; --fp-ink-3:#8C96AC; --fp-line:#E2E7F0; --fp-line-2:#EEF1F7;
    --fp-accent:#1657E0; --fp-accent-ink:#0B3DB0; --fp-accent-soft:#E8EEFF;
    --fp-glow:#25C7DA; --fp-glow-soft:#DDF6F9;
    --fp-warm:#F0A028; --fp-warm-soft:#FCEED7;
    --fp-good:#12925A; --fp-good-soft:#DEF3E9;
    --fp-warn:#B9770A; --fp-warn-soft:#FBEBD3;
    --fp-bad:#CE3A3A; --fp-bad-soft:#FAE3E3;
    --fp-shadow-sm:0 1px 2px rgba(14,22,38,.06), 0 1px 3px rgba(14,22,38,.04);
    --fp-shadow-md:0 6px 22px -8px rgba(14,22,38,.18);
    --fp-radius:16px; --fp-radius-sm:11px;
  }
  .dark{
    --fp-bg:#080C15; --fp-surface:#101827; --fp-surface-2:#0D1420;
    --fp-ink:#ECF1F9; --fp-ink-2:#98A4BD; --fp-ink-3:#606C89; --fp-line:#1E293C; --fp-line-2:#182236;
    --fp-accent:#5B8DFF; --fp-accent-ink:#A9C4FF; --fp-accent-soft:#14233F;
    --fp-glow:#37D7E7; --fp-glow-soft:#0E2B33;
    --fp-warm:#F2AE4A; --fp-warm-soft:#33260F;
    --fp-good:#37C285; --fp-good-soft:#0F2C21;
    --fp-warn:#E0A23C; --fp-warn-soft:#2E2410;
    --fp-bad:#F0645F; --fp-bad-soft:#331A1B;
    --fp-shadow-sm:0 1px 2px rgba(0,0,0,.4);
    --fp-shadow-md:0 10px 30px -10px rgba(0,0,0,.6);
  }

  .fp{
    background:var(--fp-bg); color:var(--fp-ink);
    font-family:"Public Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
    font-size:15px; line-height:1.55; -webkit-font-smoothing:antialiased;
    min-height:100vh;
  }
  .fp h1,.fp h2,.fp h3,.fp h4{font-family:"Archivo", system-ui, sans-serif; font-weight:800; letter-spacing:-.02em; line-height:1.14; color:var(--fp-ink)}
  .fp .mono{font-family:"IBM Plex Mono", ui-monospace, monospace; font-variant-numeric:tabular-nums}
  .fp-wrap{max-width:1120px; margin:0 auto; padding:0 20px 80px}

  /* ---- tabs bar ---- */
  .fp-tabsbar{
    position:sticky; top:64px; z-index:30;
    background:color-mix(in srgb, var(--fp-surface) 92%, transparent);
    backdrop-filter:blur(10px); border-bottom:1px solid var(--fp-line);
  }
  .fp-tabs{max-width:1120px; margin:0 auto; padding:0 20px; display:flex; gap:2px; overflow-x:auto}
  .fp-tab{
    display:flex; align-items:center; gap:8px; padding:12px 15px 14px; font-weight:600; font-size:13.5px;
    color:var(--fp-ink-3); border-bottom:2px solid transparent; white-space:nowrap; text-decoration:none; transition:color .12s;
  }
  .fp-tab svg{width:16px;height:16px}
  .fp-tab:hover{color:var(--fp-ink-2)}
  .fp-tab[aria-current="page"]{color:var(--fp-accent); border-color:var(--fp-accent)}

  /* ---- primitives ---- */
  .fp-eyebrow{font-size:11px; font-weight:600; letter-spacing:.15em; text-transform:uppercase; color:var(--fp-ink-3)}
  .fp-h1{font-size:clamp(25px,3.3vw,34px); margin-top:3px}
  .fp-greet{display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:16px; margin:26px 0 22px}
  .fp-greet p{color:var(--fp-ink-2); font-size:14px; margin-top:4px}

  .fp-btn{display:inline-flex; align-items:center; gap:8px; padding:11px 17px; border-radius:11px; font-weight:700; font-size:13.5px;
    background:var(--fp-accent); color:#fff; text-decoration:none; border:0; cursor:pointer;
    box-shadow:0 6px 18px -6px color-mix(in srgb, var(--fp-accent) 60%, transparent); transition:filter .12s, transform .12s}
  .fp-btn:hover{filter:brightness(1.06)} .fp-btn:active{transform:translateY(1px)}
  .fp-btn svg{width:16px;height:16px}
  .fp-btn.ghost{background:var(--fp-surface); color:var(--fp-ink); border:1px solid var(--fp-line); box-shadow:var(--fp-shadow-sm)}
  .fp-btn.ghost:hover{background:var(--fp-surface-2); filter:none}
  .fp-btn.sm{padding:8px 13px; font-size:12.5px; border-radius:9px}

  .fp-section{font-size:16px; letter-spacing:.01em; display:flex; align-items:baseline; justify-content:space-between; margin-bottom:13px}
  .fp-section a{font-family:"Public Sans"; font-weight:700; font-size:12.5px; color:var(--fp-accent); text-decoration:none}

  .fp-panel{background:var(--fp-surface); border:1px solid var(--fp-line); border-radius:var(--fp-radius); box-shadow:var(--fp-shadow-sm)}
  .fp-pad{padding:18px 20px}

  .fp-pill{display:inline-flex; align-items:center; gap:5px; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:700}
  .fp-pill .dot{width:5px;height:5px;border-radius:999px;background:currentColor}
  .fp-pill.blue{background:var(--fp-accent-soft); color:var(--fp-accent-ink)}
  .fp-pill.cyan{background:var(--fp-glow-soft); color:color-mix(in srgb, var(--fp-glow) 72%, var(--fp-ink))}
  .fp-pill.amber{background:var(--fp-warn-soft); color:var(--fp-warn)}
  .fp-pill.green{background:var(--fp-good-soft); color:var(--fp-good)}
  .fp-pill.red{background:var(--fp-bad-soft); color:var(--fp-bad)}
  .fp-pill.gray{background:var(--fp-surface-2); color:var(--fp-ink-2)}

  /* ---- ribbon ---- */
  .fp-ribbon{position:relative; overflow:hidden; border-radius:var(--fp-radius); padding:24px 26px; color:#EAF2FF;
    background:radial-gradient(520px 200px at 88% -30%, color-mix(in srgb, var(--fp-glow) 40%, transparent), transparent 70%),
      linear-gradient(120deg,#0C1B3C 0%,#123063 55%,#0E2247 100%); box-shadow:var(--fp-shadow-md)}
  .fp-ribbon .beam{position:absolute; right:-40px; top:-90px; width:260px; height:260px; border-radius:999px;
    background:radial-gradient(circle, color-mix(in srgb, var(--fp-glow) 55%, transparent), transparent 62%); filter:blur(6px)}
  .fp-ribbon .r-eyebrow{font-size:11px; font-weight:600; letter-spacing:.16em; text-transform:uppercase; color:color-mix(in srgb, var(--fp-glow) 85%, #fff)}
  .fp-ribbon .r-main{display:flex; flex-wrap:wrap; align-items:baseline; gap:12px; margin-top:8px}
  .fp-ribbon .r-main .big{font-family:"Archivo"; font-weight:800; font-size:clamp(22px,3vw,30px); letter-spacing:-.02em}
  .fp-ribbon .r-main .ord{font-family:"IBM Plex Mono"; font-size:13px; color:#B9CBEA}
  .fp-ribbon .track{display:flex; align-items:center; margin-top:18px; max-width:560px}
  .fp-ribbon .node{width:11px; height:11px; border-radius:999px; background:rgba(255,255,255,.22); flex:none}
  .fp-ribbon .node.done{background:var(--fp-glow)}
  .fp-ribbon .node.now{background:#fff; box-shadow:0 0 0 5px color-mix(in srgb, var(--fp-glow) 45%, transparent)}
  .fp-ribbon .seg{height:2px; flex:1; background:rgba(255,255,255,.18)}
  .fp-ribbon .seg.done{background:var(--fp-glow)}
  .fp-ribbon .track-labels{display:flex; justify-content:space-between; max-width:560px; margin-top:9px; font-size:10.5px; color:#9FB4D6}
  .fp-ribbon .track-labels b{color:#EAF2FF; font-weight:600}

  /* ---- stat tiles ---- */
  .fp-stats{display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:16px}
  .fp-tile{position:relative; background:var(--fp-surface); border:1px solid var(--fp-line); border-radius:var(--fp-radius-sm); padding:16px 17px;
    box-shadow:var(--fp-shadow-sm); display:flex; flex-direction:column; text-decoration:none; color:inherit; transition:border-color .15s}
  .fp-tile:hover{border-color:color-mix(in srgb, var(--fp-accent) 35%, var(--fp-line))}
  .fp-tile .t-head{display:flex; align-items:center; gap:8px; color:var(--fp-ink-2); font-size:11.5px; font-weight:600; letter-spacing:.05em; text-transform:uppercase}
  .fp-tile .t-ic{width:26px;height:26px;border-radius:8px;display:grid;place-items:center;flex:none}
  .fp-tile .t-ic svg{width:15px;height:15px}
  .fp-tile .num{font-family:"Archivo"; font-weight:800; font-size:30px; letter-spacing:-.03em; margin-top:8px}
  .fp-tile .sub{font-size:12px; color:var(--fp-ink-3)}
  .fp-tile.attn{border-color:color-mix(in srgb, var(--fp-warm) 45%, var(--fp-line))}
  .fp-tile.attn::after{content:""; position:absolute; inset:0 auto 0 0; width:3px; border-radius:3px 0 0 3px; background:var(--fp-warm)}
  .ic-glow{background:var(--fp-glow-soft); color:var(--fp-glow)}
  .ic-accent{background:var(--fp-accent-soft); color:var(--fp-accent-ink)}
  .ic-warm{background:var(--fp-warm-soft); color:var(--fp-warn)}
  .ic-mut{background:var(--fp-surface-2); color:var(--fp-ink-2)}

  .fp-grid2{display:grid; grid-template-columns:1.55fr 1fr; gap:22px; margin-top:30px}

  /* ---- list rows ---- */
  .fp-row{display:flex; align-items:center; gap:14px; padding:15px 20px; border-top:1px solid var(--fp-line-2); text-decoration:none; color:inherit}
  .fp-row:first-child{border-top:0}
  .fp-row:hover{background:var(--fp-surface-2)}
  .fp-obox{width:42px; height:42px; border-radius:11px; flex:none; display:grid; place-items:center;
    background:linear-gradient(150deg, var(--fp-accent-soft), var(--fp-glow-soft)); color:var(--fp-accent-ink)}
  .fp-obox svg{width:19px;height:19px}
  .fp-row .o-body{min-width:0; flex:1}
  .fp-row .o-t{display:flex; align-items:center; gap:8px; flex-wrap:wrap}
  .fp-row .o-t b{font-family:"Archivo"; font-weight:700; font-size:14.5px}
  .fp-row .o-meta{font-size:12px; color:var(--fp-ink-3); margin-top:1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap}
  .fp-row .o-amt{text-align:right; flex:none}
  .fp-row .o-amt b{font-family:"Archivo"; font-weight:800; font-size:15px}
  .fp-row .o-amt span{display:block; font-size:11px; color:var(--fp-ink-3)}
  .fp-chev{width:16px;height:16px;color:var(--fp-ink-3);flex:none}

  /* order card (list view) */
  .fp-ocard{display:block; border:1px solid var(--fp-line); background:var(--fp-surface); border-radius:var(--fp-radius); padding:16px 18px;
    box-shadow:var(--fp-shadow-sm); text-decoration:none; color:inherit; transition:border-color .15s, box-shadow .15s}
  .fp-ocard:hover{border-color:color-mix(in srgb, var(--fp-accent) 30%, var(--fp-line)); box-shadow:var(--fp-shadow-md)}
  .fp-ocard .oc-foot{display:flex; flex-wrap:wrap; align-items:center; gap:16px 20px; border-top:1px solid var(--fp-line-2); margin-top:12px; padding-top:12px; font-size:12px; color:var(--fp-ink-2)}
  .fp-ocard .oc-foot svg{width:14px;height:14px}

  /* ---- quick actions ---- */
  .fp-qa{display:flex; align-items:center; gap:12px; padding:13px 16px; border-top:1px solid var(--fp-line-2); width:100%; text-align:left; background:none; border-left:0;border-right:0;border-bottom:0; cursor:pointer; text-decoration:none; color:inherit}
  .fp-qa:first-of-type{border-top:0}
  .fp-qa:hover{background:var(--fp-surface-2)}
  .fp-qa .qa-ic{width:34px;height:34px;border-radius:10px;background:var(--fp-surface-2);border:1px solid var(--fp-line);display:grid;place-items:center;color:var(--fp-ink-2);flex:none}
  .fp-qa .qa-ic svg{width:16px;height:16px}
  .fp-qa b{font-weight:600; font-size:13.5px; display:block}
  .fp-qa span{font-size:11.5px; color:var(--fp-ink-3)}
  .fp-railcta{margin:16px; padding:15px; border-radius:12px; background:var(--fp-surface-2); border:1px dashed var(--fp-line); text-align:center}
  .fp-railcta p{font-size:12.5px; color:var(--fp-ink-2); margin-bottom:9px}

  /* ---- order detail ---- */
  .fp-back{display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--fp-ink-2); margin-bottom:16px; text-decoration:none}
  .fp-back:hover{color:var(--fp-ink)}
  .fp-back svg{width:15px;height:15px}
  .fp-odhead{display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:14px; padding:20px 22px}
  .fp-odhead h1{font-size:23px; display:flex; align-items:center; gap:10px; flex-wrap:wrap}
  .fp-odhead .when{font-size:13px; color:var(--fp-ink-2); margin-top:4px}
  .fp-odgrid{display:grid; grid-template-columns:1.5fr 1fr; gap:18px; margin-top:16px}
  .fp-col{display:flex; flex-direction:column; gap:18px}

  .fp-timeline{list-style:none; position:relative; padding:4px 0; margin:0}
  .fp-timeline li{position:relative; display:flex; gap:15px; padding-bottom:22px}
  .fp-timeline li:last-child{padding-bottom:0}
  .fp-timeline li .line{position:absolute; left:15px; top:30px; bottom:-4px; width:2px; background:var(--fp-line); transform:translateX(-50%)}
  .fp-timeline li.done .line{background:var(--fp-accent)}
  .fp-timeline li .knob{width:30px;height:30px;border-radius:999px;flex:none;display:grid;place-items:center;border:2px solid var(--fp-line);background:var(--fp-surface);color:var(--fp-ink-3);z-index:1}
  .fp-timeline li .knob svg{width:14px;height:14px}
  .fp-timeline li .knob .pt{width:7px;height:7px;border-radius:999px;background:currentColor}
  .fp-timeline li.done .knob{border-color:var(--fp-accent); background:var(--fp-accent); color:#fff}
  .fp-timeline li.now .knob{border-color:var(--fp-accent); color:var(--fp-accent); background:var(--fp-surface); box-shadow:0 0 0 5px var(--fp-accent-soft)}
  .fp-timeline li.now .knob .pt{background:var(--fp-accent); animation:fp-ping 1.6s ease-out infinite}
  @keyframes fp-ping{0%{box-shadow:0 0 0 0 color-mix(in srgb,var(--fp-accent) 55%,transparent)}70%,100%{box-shadow:0 0 0 9px transparent}}
  @media (prefers-reduced-motion:reduce){.fp-timeline li.now .knob .pt{animation:none}}
  .fp-timeline li .txt{padding-top:4px}
  .fp-timeline li .txt b{font-weight:700; font-size:14px}
  .fp-timeline li.pending .txt b{color:var(--fp-ink-3); font-weight:600}
  .fp-timeline li .txt time{display:block; font-size:11.5px; color:var(--fp-ink-3); margin-top:2px; font-family:"IBM Plex Mono"}
  .fp-timeline li.now .txt time{color:var(--fp-accent); font-weight:600}

  .fp-lines{list-style:none; margin:0}
  .fp-lines li{display:flex; align-items:flex-start; gap:12px; padding:13px 20px; border-top:1px solid var(--fp-line-2)}
  .fp-lines li:first-child{border-top:0}
  .fp-qty{min-width:34px; height:26px; padding:0 7px; border-radius:8px; background:var(--fp-surface-2); border:1px solid var(--fp-line); display:grid; place-items:center;
    font-family:"IBM Plex Mono"; font-weight:600; font-size:12px; flex:none; margin-top:1px}
  .fp-lines .li-body{flex:1; min-width:0}
  .fp-lines .li-body b{font-weight:600; font-size:13.5px}
  .fp-lines .li-body .u{font-size:11.5px; color:var(--fp-ink-3); margin-top:2px}
  .fp-lines .li-amt{font-family:"Archivo"; font-weight:800; font-size:14px; white-space:nowrap}
  .fp-totals{padding:15px 20px; border-top:1px solid var(--fp-line-2); font-size:13.5px}
  .fp-totals .tr{display:flex; justify-content:space-between; color:var(--fp-ink-2); padding:3px 0}
  .fp-totals .tr.grand{border-top:1px solid var(--fp-line-2); margin-top:6px; padding-top:9px; font-family:"Archivo"; font-weight:800; font-size:16px; color:var(--fp-ink)}

  .fp-kv{display:flex; flex-direction:column; gap:13px; margin:0}
  .fp-kv dt{font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--fp-ink-3)}
  .fp-kv dd{font-weight:600; font-size:13.5px; display:flex; gap:7px; align-items:flex-start; margin:0}
  .fp-kv dd svg{width:15px;height:15px;color:var(--fp-ink-3);flex:none;margin-top:2px}

  /* ---- invoices table ---- */
  .fp-filters{display:flex; gap:8px; flex-wrap:wrap; margin:18px 0}
  .fp-fpill{padding:8px 14px; border-radius:999px; font-size:12.5px; font-weight:700; border:1px solid var(--fp-line); background:var(--fp-surface); color:var(--fp-ink-2); cursor:pointer}
  .fp-fpill[aria-pressed="true"]{background:var(--fp-accent); border-color:var(--fp-accent); color:#fff}
  .fp-tablewrap{overflow-x:auto}
  table.fp-inv{width:100%; border-collapse:collapse; font-size:13.5px}
  table.fp-inv thead th{text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--fp-ink-3); padding:11px 16px; border-bottom:1px solid var(--fp-line); white-space:nowrap}
  table.fp-inv th.r, table.fp-inv td.r{text-align:right}
  table.fp-inv tbody td{padding:14px 16px; border-bottom:1px solid var(--fp-line-2); white-space:nowrap}
  table.fp-inv tbody tr:hover{background:var(--fp-surface-2)}
  table.fp-inv .fnum{font-family:"IBM Plex Mono"; font-weight:600}
  table.fp-inv .amt{font-family:"Archivo"; font-weight:800}

  .fp-empty{padding:56px 24px; text-align:center}
  .fp-empty svg{width:38px;height:38px;margin:0 auto 10px;color:var(--fp-line);display:block}
  .fp-empty p{font-weight:600; color:var(--fp-ink)}
  .fp-empty span{font-size:13px; color:var(--fp-ink-3)}

  .fp-note{margin-top:32px; padding:15px 18px; border-radius:12px; background:var(--fp-accent-soft);
    border:1px solid color-mix(in srgb, var(--fp-accent) 25%, var(--fp-line)); font-size:12.5px; color:var(--fp-accent-ink); display:flex; gap:10px}
  .fp-note svg{width:17px;height:17px;flex:none;margin-top:1px}

  .fp-search{position:relative; flex:1; min-width:200px}
  .fp-search svg{position:absolute; left:13px; top:50%; transform:translateY(-50%); width:16px; height:16px; color:var(--fp-ink-3)}
  .fp-search input{width:100%; padding:11px 14px 11px 38px; border-radius:11px; border:1px solid var(--fp-line); background:var(--fp-surface); color:var(--fp-ink); font:inherit; box-shadow:var(--fp-shadow-sm)}
  .fp-search input:focus{outline:2px solid var(--fp-accent); outline-offset:1px}

  .fp-pagination{margin-top:22px}

  @media (max-width:880px){
    .fp-stats{grid-template-columns:1fr 1fr}
    .fp-grid2,.fp-odgrid{grid-template-columns:1fr}
  }
  @media (max-width:560px){ .fp-stats{grid-template-columns:1fr} }
</style>
@endpush
@endonce

@php
    $tabs = [
        'dashboard' => ['label' => 'Inicio',      'route' => 'tenant.client.dashboard', 'icon' => 'M3 10.5 12 4l9 6.5V20a1 1 0 0 1-1 1h-4v-6h-8v6H4a1 1 0 0 1-1-1Z'],
        'orders'    => ['label' => 'Mis Pedidos',  'route' => 'tenant.client.orders',    'icon' => 'M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 11H4Z'],
        'invoices'  => ['label' => 'Mis Facturas', 'route' => 'tenant.client.invoices',  'icon' => 'M8 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6H8Z M9 13h6M9 17h6M13 3v6h6'],
        'catalog'   => ['label' => 'Catálogo',     'route' => 'tenant.client.portal',    'icon' => 'M20 7 12 3 4 7m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
    ];
@endphp

<div class="fp-tabsbar">
    <nav class="fp-tabs" aria-label="Panel del cliente">
        @foreach ($tabs as $key => $tab)
            <a href="{{ route($tab['route']) }}" wire:navigate
               @if($key === $active) aria-current="page" @endif
               class="fp-tab">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="{{ $tab['icon'] }}" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
