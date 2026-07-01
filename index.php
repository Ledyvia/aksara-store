<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

// Toko hanya bisa diakses setelah login. Kalau belum login, langsung
// arahkan ke halaman Masuk — jadi yang tampil pertama kali cuma
// halaman login, bukan katalog + form pesan.
if (!is_logged_in()) {
    header('Location: auth/login.php?redirect=shop');
    exit;
}

// Ambil katalog novel langsung dari database, supaya perubahan lewat
// dashboard admin (tambah/edit/hapus) langsung tampil di toko.
$rows = $pdo->query("SELECT * FROM novels WHERE status = 'active' ORDER BY created_at DESC")->fetchAll();

$novelsForJs = array_map(function ($n) {
    return [
        'id'     => (int)$n['id'],
        'title'  => $n['title'],
        'author' => $n['author'],
        'genre'  => $n['genre'],
        'price'  => (int)$n['price'],
        'orig'   => $n['orig_price'] !== null ? (int)$n['orig_price'] : null,
        'rating' => (float)$n['rating'],
        'badge'  => $n['badge'] ?: null,
        'icon'   => $n['icon'],
        'bg'     => $n['cover_bg'] ?: 'linear-gradient(150deg,#0f0f1a,#1a1a2e,#16213e)',
        'color'  => $n['cover_color'],
        'desc'   => $n['description'],
        'tags'   => $n['tags'] ? array_map('trim', explode(',', $n['tags'])) : [],
    ];
}, $rows);

$isLoggedIn = is_logged_in();
$userRole   = current_role();
$userName   = $_SESSION['full_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Aksara Store — Novel Digital & Ebook Premium</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --bg:#08080f; --bg2:#0d0d18; --bg3:#12121f; --card:#0f0f1c;
      --gold:#c8a84b; --gold2:#e5c97a; --text:#e8e2d4; --muted:#6b6b88;
      --border:rgba(200,168,75,.14); --red:#e05555;
      --serif:'Cormorant Garamond',Georgia,serif;
      --sans:'Outfit',sans-serif;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{background:var(--bg);color:var(--text);font-family:var(--sans);font-weight:300;overflow-x:hidden}
    body::after{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");pointer-events:none;z-index:9999;opacity:.4}
    ::-webkit-scrollbar{width:3px}
    ::-webkit-scrollbar-track{background:var(--bg)}
    ::-webkit-scrollbar-thumb{background:var(--gold)}

    /* ── NAVBAR ── */
    .navbar{position:fixed;top:0;width:100%;z-index:1000;padding:1.2rem 0;transition:all .4s}
    .navbar.scrolled{background:rgba(8,8,15,.93);backdrop-filter:blur(24px);padding:.8rem 0;border-bottom:1px solid var(--border)}
    .nav-brand{font-family:var(--serif);font-size:1.7rem;font-weight:700;color:var(--gold);text-decoration:none}
    .nav-brand sup{font-size:.5rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);vertical-align:super;margin-left:3px}
    .nav-link-i{color:var(--muted);text-decoration:none;font-size:.75rem;letter-spacing:1.8px;text-transform:uppercase;padding:.4rem .8rem;transition:color .3s}
    .nav-link-i:hover{color:var(--gold)}
    .btn-cta{background:var(--gold);color:#08080f;border:none;border-radius:1px;padding:.5rem 1.4rem;font-size:.74rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;transition:all .3s;display:inline-block}
    .btn-cta:hover{background:var(--gold2);color:#08080f;transform:translateY(-1px)}
    .cart-btn{position:relative;background:transparent;border:1px solid var(--border);color:var(--text);width:40px;height:40px;border-radius:1px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .3s;font-size:1.1rem}
    .cart-btn:hover{border-color:var(--gold);color:var(--gold)}
    .cart-count{position:absolute;top:-7px;right:-7px;background:var(--gold);color:#08080f;font-size:.6rem;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;display:none}
    .cart-count.show{display:flex}

    /* ── HERO ── */
    .hero{min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;padding:8rem 0 5rem}
    .hero-ambient{position:absolute;inset:0;background:radial-gradient(ellipse 55% 55% at 75% 45%,rgba(200,168,75,.07) 0%,transparent 65%),radial-gradient(ellipse 35% 50% at 15% 70%,rgba(80,60,160,.05) 0%,transparent 60%);pointer-events:none}
    .hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(200,168,75,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(200,168,75,.03) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 30%,transparent 80%)}
    .eyebrow{display:inline-flex;align-items:center;gap:.6rem;font-size:.67rem;letter-spacing:3px;text-transform:uppercase;color:var(--gold);border:1px solid var(--border);padding:.4rem 1.1rem;margin-bottom:2rem;animation:fadeUp .7s ease both}
    .dot-pulse{width:5px;height:5px;background:var(--gold);border-radius:50%;animation:pulse 2s infinite}
    @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.7)}}
    .hero-h1{font-family:var(--serif);font-size:clamp(3rem,6.5vw,6.5rem);font-weight:700;line-height:1.0;letter-spacing:-1px;animation:fadeUp .8s .08s ease both}
    .hero-h1 .it{font-style:italic;color:var(--gold)}
    .hero-h1 .ol{-webkit-text-stroke:1px rgba(200,168,75,.4);color:transparent}
    .hero-sub{font-size:1rem;color:var(--muted);max-width:440px;line-height:1.85;margin:1.8rem 0 2.5rem;animation:fadeUp .8s .16s ease both}
    .hero-btns{animation:fadeUp .8s .24s ease both}
    .btn-gold{background:var(--gold);color:#08080f;border:none;border-radius:1px;padding:.9rem 2.2rem;font-size:.8rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;transition:all .35s;display:inline-block;cursor:pointer}
    .btn-gold:hover{background:var(--gold2);color:#08080f;transform:translateY(-2px);box-shadow:0 10px 35px rgba(200,168,75,.25)}
    .btn-ghost{background:transparent;color:var(--text);border:1px solid rgba(232,226,212,.15);border-radius:1px;padding:.9rem 2.2rem;font-size:.8rem;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;transition:all .35s;display:inline-block}
    .btn-ghost:hover{border-color:var(--gold);color:var(--gold)}
    .hero-metrics{display:flex;gap:2.5rem;margin-top:4rem;padding-top:2rem;border-top:1px solid var(--border);animation:fadeUp .8s .32s ease both}
    .mval{font-family:var(--serif);font-size:2.2rem;font-weight:700;color:var(--gold);line-height:1}
    .mlbl{font-size:.67rem;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-top:.3rem}

    /* Hero books */
    .books-wrap{position:relative;width:320px;height:420px;margin:0 auto;animation:fadeUp .9s .1s ease both}
    .bk{position:absolute;border-radius:3px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.6);transition:transform .4s ease;border:1px solid var(--border)}
    .bk:hover{transform:translateY(-6px) !important}
    .bk-main{width:180px;aspect-ratio:2/3;top:0;left:50%;transform:translateX(-50%);z-index:3;border-color:rgba(200,168,75,.25)}
    .bk-l{width:130px;aspect-ratio:2/3;top:50px;left:0;transform:rotate(-8deg);z-index:2}
    .bk-r{width:130px;aspect-ratio:2/3;top:60px;right:0;transform:rotate(6deg);z-index:2}
    .bk-face{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1rem .7rem;text-align:center;position:relative;overflow:hidden}
    .bk-face::before{content:'';position:absolute;left:0;top:0;bottom:0;width:6px;background:rgba(0,0,0,.3)}
    .bk-ico{font-size:2rem;margin-bottom:.6rem;position:relative;z-index:1}
    .bk-ttl{font-family:var(--serif);font-size:.85rem;font-weight:700;line-height:1.25;position:relative;z-index:1}
    .bk-aut{font-size:.58rem;letter-spacing:1.5px;text-transform:uppercase;margin-top:.4rem;position:relative;z-index:1;opacity:.7}
    .fl-badge{position:absolute;background:var(--gold);color:#08080f;font-size:.58rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:.3rem .7rem;top:1rem;right:-1.5rem;z-index:10;animation:float 3s ease-in-out infinite}
    .fl-badge2{position:absolute;background:var(--card);border:1px solid var(--border);color:var(--text);font-size:.6rem;padding:.5rem .9rem;bottom:2rem;left:-1rem;z-index:10;animation:float 3.5s 1s ease-in-out infinite}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}

    /* ── TICKER ── */
    .ticker{background:var(--gold);padding:.7rem 0;overflow:hidden;white-space:nowrap}
    .ticker-inner{display:inline-block;animation:tick 25s linear infinite;font-size:.68rem;font-weight:600;letter-spacing:2.5px;text-transform:uppercase;color:#08080f}
    .ticker-inner span{margin:0 2.5rem}
    @keyframes tick{from{transform:translateX(0)}to{transform:translateX(-50%)}}

    /* ── SECTIONS ── */
    .section{padding:6rem 0}
    .section-alt{background:var(--bg2)}
    .tag-line{display:inline-flex;align-items:center;gap:.5rem;font-size:.66rem;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:.8rem}
    .tag-line::before{content:'';width:20px;height:1px;background:var(--gold)}
    .sec-h{font-family:var(--serif);font-size:clamp(2rem,3.5vw,3rem);font-weight:700;line-height:1.1}
    .sec-h em{font-style:italic;color:var(--gold)}
    .divider{width:40px;height:1.5px;background:var(--gold);margin:1.2rem 0}
    .divider.mx-auto{margin-left:auto;margin-right:auto}

    /* ── STEPS ── */
    .step-num{font-family:var(--serif);font-size:4rem;font-weight:700;color:rgba(200,168,75,.1);line-height:1;margin-bottom:.5rem}
    .step-icon{width:52px;height:52px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:1rem;color:var(--gold);background:rgba(200,168,75,.04)}
    .step-title{font-family:var(--serif);font-size:1.2rem;font-weight:600;margin-bottom:.5rem}
    .step-desc{font-size:.85rem;color:var(--muted);line-height:1.75}
    .step-conn{position:absolute;top:2.5rem;right:-15%;width:30%;height:1px;background:linear-gradient(90deg,var(--border),transparent)}

    /* ── SEARCH BAR ── */
    .search-wrap{background:var(--card);border:1px solid var(--border);border-radius:2px;padding:.7rem 1.2rem;display:flex;align-items:center;gap:.8rem;margin-bottom:1.5rem;transition:border-color .3s}
    .search-wrap:focus-within{border-color:rgba(200,168,75,.4)}
    .search-wrap i{color:var(--muted);font-size:1rem}
    .search-wrap input{background:transparent;border:none;outline:none;color:var(--text);font-family:var(--sans);font-size:.9rem;flex:1}
    .search-wrap input::placeholder{color:var(--muted)}
    .search-wrap .clear-btn{background:transparent;border:none;color:var(--muted);cursor:pointer;font-size:.8rem;padding:0;transition:color .3s;display:none}
    .search-wrap .clear-btn.show{display:block}
    .search-wrap .clear-btn:hover{color:var(--gold)}

    /* ── FILTER ROW ── */
    .filter-row{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:1rem}
    .pill{background:transparent;border:1px solid var(--border);color:var(--muted);padding:.38rem 1rem;border-radius:1px;font-size:.7rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:all .3s;font-family:var(--sans)}
    .pill:hover,.pill.active{background:var(--gold);border-color:var(--gold);color:#08080f;font-weight:600}
    .sort-select{background:var(--card);border:1px solid var(--border);color:var(--muted);padding:.38rem 1rem;border-radius:1px;font-size:.7rem;letter-spacing:1px;cursor:pointer;font-family:var(--sans);outline:none;transition:all .3s}
    .sort-select:focus{border-color:rgba(200,168,75,.4);color:var(--text)}
    .result-count{font-size:.78rem;color:var(--muted);margin-left:auto;align-self:center}

    /* ── NOVEL CARD ── */
    .ncard{background:var(--card);border:1px solid var(--border);border-radius:2px;overflow:hidden;transition:all .4s cubic-bezier(.25,.46,.45,.94);height:100%;cursor:pointer}
    .ncard:hover{transform:translateY(-6px);border-color:rgba(200,168,75,.35);box-shadow:0 18px 45px rgba(0,0,0,.45)}
    .ncard-cover{width:100%;aspect-ratio:3/4;overflow:hidden;position:relative}
    .cover-art{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;overflow:hidden;transition:transform .4s ease}
    .ncard:hover .cover-art{transform:scale(1.04)}
    .ca-ico{font-size:2.2rem;position:relative;z-index:1;margin-bottom:.5rem}
    .ca-ttl{font-family:var(--serif);font-size:.9rem;font-weight:700;text-align:center;padding:0 .8rem;line-height:1.3;position:relative;z-index:1}
    .ca-aut{font-size:.58rem;letter-spacing:1.5px;text-transform:uppercase;margin-top:.35rem;position:relative;z-index:1;opacity:.7}
    .nc-badge{position:absolute;top:.8rem;left:.8rem;font-size:.55rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:.2rem .55rem;z-index:5}
    .b-new{background:rgba(200,168,75,.9);color:#08080f}
    .b-hot{background:rgba(220,100,60,.9);color:#fff}
    .b-disc{background:rgba(80,160,80,.9);color:#fff}
    .nc-body{padding:1.1rem 1.2rem 1.4rem}
    .nc-genre{font-size:.62rem;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:.35rem}
    .nc-title{font-family:var(--serif);font-size:.98rem;font-weight:700;line-height:1.3;margin-bottom:.25rem}
    .nc-author{font-size:.75rem;color:var(--muted);margin-bottom:.8rem}
    .nc-price{font-family:var(--serif);font-size:1.1rem;font-weight:700;color:var(--gold)}
    .nc-orig{font-size:.75rem;color:var(--muted);text-decoration:line-through;margin-left:.4rem}
    .nc-rating{font-size:.72rem;color:var(--gold);margin-bottom:.3rem}
    .nc-rating span{color:var(--muted);margin-left:.3rem}
    .nc-btn{display:block;width:100%;background:transparent;border:1px solid var(--border);color:var(--text);padding:.6rem;font-size:.72rem;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:all .3s;margin-top:.9rem;font-family:var(--sans);border-radius:0}
    .nc-btn:hover{background:var(--gold);border-color:var(--gold);color:#08080f;font-weight:600}
    .no-results{text-align:center;padding:4rem 2rem;color:var(--muted)}
    .no-results i{font-size:3rem;display:block;margin-bottom:1rem;opacity:.3}

    /* ── FEATURES ── */
    .feat-card{background:var(--card);border:1px solid var(--border);padding:2rem 1.8rem;border-radius:2px;height:100%;transition:all .35s;position:relative;overflow:hidden}
    .feat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:var(--gold);transform:scaleX(0);transition:transform .35s}
    .feat-card:hover{border-color:rgba(200,168,75,.3);transform:translateY(-4px)}
    .feat-card:hover::after{transform:scaleX(1)}
    .feat-ico{font-size:1.6rem;color:var(--gold);margin-bottom:1.2rem;display:block}
    .feat-title{font-family:var(--serif);font-size:1.1rem;font-weight:600;margin-bottom:.6rem}
    .feat-desc{font-size:.83rem;color:var(--muted);line-height:1.75}

    /* ── TESTIMONIALS ── */
    .t-card{background:var(--card);border:1px solid var(--border);padding:2rem;border-radius:2px;height:100%;transition:all .35s}
    .t-card:hover{border-color:rgba(200,168,75,.3);transform:translateY(-3px)}
    .t-q{font-family:var(--serif);font-size:3.5rem;color:var(--gold);opacity:.3;line-height:1;margin-bottom:.3rem}
    .t-stars{color:var(--gold);font-size:.78rem;margin-bottom:.8rem;letter-spacing:2px}
    .t-text{font-size:.85rem;color:var(--muted);line-height:1.85;font-style:italic;margin-bottom:1.4rem}
    .t-av{width:38px;height:38px;background:var(--bg3);border:1px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:1rem;color:var(--gold);margin-right:.8rem;flex-shrink:0}
    .t-name{font-weight:500;font-size:.88rem}
    .t-role{font-size:.72rem;color:var(--muted)}

    /* ── CTA BAND ── */
    .cta-band{background:linear-gradient(135deg,#0d0c18,#141228,#0d0c18);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:5rem 0;position:relative;overflow:hidden}
    .cta-band::before{content:'';position:absolute;top:-50%;left:30%;width:600px;height:600px;background:radial-gradient(circle,rgba(200,168,75,.06) 0%,transparent 65%);pointer-events:none}
    .cta-h{font-family:var(--serif);font-size:clamp(2rem,4vw,3.5rem);font-weight:700;line-height:1.1}
    .cta-h em{font-style:italic;color:var(--gold)}

    /* ── CONTACT ── */
    .c-input{background:var(--bg3)!important;border:1px solid var(--border)!important;color:var(--text)!important;border-radius:1px!important;padding:.65rem 1rem;font-family:var(--sans);font-size:.85rem}
    .c-input::placeholder{color:var(--muted)!important}
    .c-input:focus{box-shadow:0 0 0 2px rgba(200,168,75,.15)!important;border-color:var(--gold)!important}
    .c-icon{width:42px;height:42px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--gold);flex-shrink:0}

    /* ── FOOTER ── */
    footer{background:var(--bg);border-top:1px solid var(--border);padding:3.5rem 0 1.5rem}
    .f-brand{font-family:var(--serif);font-size:1.6rem;font-weight:700;color:var(--gold)}
    .f-desc{font-size:.82rem;color:var(--muted);line-height:1.8;max-width:260px;margin-top:.7rem}
    .f-head{font-size:.65rem;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:1.1rem}
    .f-link{display:block;font-size:.82rem;color:var(--muted);text-decoration:none;margin-bottom:.55rem;transition:color .3s}
    .f-link:hover{color:var(--gold)}
    .soc{width:34px;height:34px;border:1px solid var(--border);display:inline-flex;align-items:center;justify-content:center;color:var(--muted);text-decoration:none;margin-right:.4rem;font-size:.88rem;transition:all .3s}
    .soc:hover{border-color:var(--gold);color:var(--gold)}
    .f-bottom{border-top:1px solid var(--border);padding-top:1.5rem;margin-top:2.5rem;font-size:.75rem;color:var(--muted)}

    /* ── CART SIDEBAR ── */
    .cart-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2000;opacity:0;pointer-events:none;transition:opacity .35s;backdrop-filter:blur(4px)}
    .cart-overlay.open{opacity:1;pointer-events:all}
    .cart-sidebar{position:fixed;top:0;right:0;width:420px;max-width:100vw;height:100vh;background:var(--bg2);border-left:1px solid var(--border);z-index:2001;transform:translateX(100%);transition:transform .4s cubic-bezier(.25,.46,.45,.94);display:flex;flex-direction:column}
    .cart-sidebar.open{transform:translateX(0)}
    .cart-header{padding:1.5rem 1.8rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
    .cart-title{font-family:var(--serif);font-size:1.4rem;font-weight:700}
    .cart-close{background:transparent;border:1px solid var(--border);color:var(--muted);width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .3s;font-size:1rem}
    .cart-close:hover{border-color:var(--gold);color:var(--gold)}
    .cart-body{flex:1;overflow-y:auto;padding:1.5rem 1.8rem}
    .cart-body::-webkit-scrollbar{width:2px}
    .cart-empty{text-align:center;padding:4rem 2rem;color:var(--muted)}
    .cart-empty i{font-size:3rem;display:block;margin-bottom:1rem;opacity:.3}
    .cart-item{display:flex;gap:1rem;padding:1rem 0;border-bottom:1px solid var(--border);align-items:flex-start}
    .ci-cover{width:60px;height:80px;border-radius:2px;overflow:hidden;flex-shrink:0;border:1px solid var(--border)}
    .ci-info{flex:1}
    .ci-title{font-family:var(--serif);font-size:.9rem;font-weight:700;margin-bottom:.2rem;line-height:1.3}
    .ci-author{font-size:.72rem;color:var(--muted);margin-bottom:.5rem}
    .ci-price{font-family:var(--serif);font-size:1rem;color:var(--gold);font-weight:700}
    .ci-remove{background:transparent;border:none;color:var(--muted);cursor:pointer;font-size:.85rem;transition:color .3s;padding:0}
    .ci-remove:hover{color:var(--red)}
    .cart-footer{padding:1.5rem 1.8rem;border-top:1px solid var(--border);flex-shrink:0}
    .cart-total-row{display:flex;justify-content:space-between;margin-bottom:.5rem;font-size:.85rem;color:var(--muted)}
    .cart-grand{display:flex;justify-content:space-between;font-family:var(--serif);font-size:1.2rem;font-weight:700;padding-top:.8rem;border-top:1px solid var(--border);margin-bottom:1.2rem}
    .cart-grand span:last-child{color:var(--gold)}

    /* ── CHECKOUT MODAL ── */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:3000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .35s;backdrop-filter:blur(6px);padding:1rem}
    .modal-overlay.open{opacity:1;pointer-events:all}
    .modal-box{background:var(--bg2);border:1px solid var(--border);border-radius:2px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;transform:translateY(30px);transition:transform .4s ease;animation:none}
    .modal-overlay.open .modal-box{transform:translateY(0)}
    .modal-box::-webkit-scrollbar{width:2px}
    .modal-head{padding:1.5rem 2rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
    .modal-title{font-family:var(--serif);font-size:1.3rem;font-weight:700}
    .modal-close{background:transparent;border:1px solid var(--border);color:var(--muted);width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .3s;font-size:.9rem}
    .modal-close:hover{border-color:var(--gold);color:var(--gold)}
    .modal-body{padding:2rem}
    .step-indicator{display:flex;gap:0;margin-bottom:2rem}
    .si-step{flex:1;text-align:center;position:relative}
    .si-step::after{content:'';position:absolute;top:14px;left:50%;width:100%;height:1px;background:var(--border)}
    .si-step:last-child::after{display:none}
    .si-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:600;margin:0 auto .4rem;border:1px solid var(--border);color:var(--muted);background:var(--bg2);position:relative;z-index:1;transition:all .3s}
    .si-num.active{border-color:var(--gold);color:var(--gold);background:rgba(200,168,75,.1)}
    .si-num.done{border-color:var(--gold);background:var(--gold);color:#08080f}
    .si-label{font-size:.62rem;letter-spacing:1px;text-transform:uppercase;color:var(--muted)}
    .si-label.active{color:var(--gold)}
    .form-step{display:none}
    .form-step.active{display:block}
    .f-label{font-size:.68rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:.45rem}
    .f-inp{background:var(--bg3)!important;border:1px solid var(--border)!important;color:var(--text)!important;border-radius:1px!important;padding:.7rem 1rem;font-family:var(--sans);font-size:.88rem;width:100%}
    .f-inp::placeholder{color:var(--muted)!important}
    .f-inp:focus{outline:none;border-color:rgba(200,168,75,.4)!important;box-shadow:0 0 0 2px rgba(200,168,75,.1)!important}
    .pay-method{display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem;margin-top:.5rem}
    .pay-opt{border:1px solid var(--border);padding:.8rem .5rem;text-align:center;cursor:pointer;transition:all .3s;border-radius:1px}
    .pay-opt:hover,.pay-opt.selected{border-color:var(--gold);background:rgba(200,168,75,.07)}
    .pay-opt .pay-ico{font-size:1.5rem;display:block;margin-bottom:.3rem}
    .pay-opt .pay-name{font-size:.68rem;letter-spacing:1px;text-transform:uppercase;color:var(--muted)}
    .pay-opt.selected .pay-name{color:var(--gold)}
    .order-summary-item{display:flex;justify-content:space-between;font-size:.83rem;padding:.5rem 0;border-bottom:1px solid var(--border);color:var(--muted)}
    .order-summary-item:last-of-type{border:none}
    .order-total-row{display:flex;justify-content:space-between;font-family:var(--serif);font-size:1.1rem;font-weight:700;padding-top:.8rem;border-top:1px solid var(--border)}
    .order-total-row span:last-child{color:var(--gold)}
    .success-screen{text-align:center;padding:2rem 1rem;display:none}
    .success-screen.show{display:block}
    .success-ico{font-size:4rem;margin-bottom:1rem;animation:bounceIn .6s ease}
    @keyframes bounceIn{0%{transform:scale(0)}60%{transform:scale(1.15)}100%{transform:scale(1)}}

    /* ── NOVEL DETAIL MODAL ── */
    .detail-overlay{position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:3500;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .35s;backdrop-filter:blur(8px);padding:1rem}
    .detail-overlay.open{opacity:1;pointer-events:all}
    .detail-box{background:var(--bg2);border:1px solid var(--border);border-radius:2px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto;transform:scale(.95);transition:transform .4s ease}
    .detail-overlay.open .detail-box{transform:scale(1)}
    .detail-box::-webkit-scrollbar{width:2px}
    .detail-cover{width:100%;height:280px;overflow:hidden;position:relative;flex-shrink:0}
    .detail-cover .cover-art{height:100%}
    .detail-content{padding:2rem}
    .detail-genre{font-size:.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:.5rem}
    .detail-title{font-family:var(--serif);font-size:1.8rem;font-weight:700;line-height:1.1;margin-bottom:.4rem}
    .detail-author{font-size:.85rem;color:var(--muted);margin-bottom:.8rem}
    .detail-rating{color:var(--gold);font-size:.85rem;margin-bottom:1rem}
    .detail-desc{font-size:.88rem;color:var(--muted);line-height:1.85;margin-bottom:1.5rem}
    .detail-meta{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem}
    .detail-tag{border:1px solid var(--border);font-size:.65rem;letter-spacing:1px;text-transform:uppercase;padding:.3rem .7rem;color:var(--muted)}
    .detail-price-row{display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem}
    .detail-price{font-family:var(--serif);font-size:1.8rem;font-weight:700;color:var(--gold)}
    .detail-orig{font-size:.9rem;color:var(--muted);text-decoration:line-through}
    .detail-btns{display:flex;gap:1rem}
    .btn-detail-buy{flex:1;background:var(--gold);color:#08080f;border:none;padding:.85rem;font-size:.8rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:all .3s;font-family:var(--sans)}
    .btn-detail-buy:hover{background:var(--gold2)}
    .btn-detail-wish{background:transparent;border:1px solid var(--border);color:var(--muted);padding:.85rem 1.2rem;cursor:pointer;transition:all .3s;font-size:1rem}
    .btn-detail-wish:hover{border-color:var(--gold);color:var(--gold)}

    /* ── TOAST ── */
    #toast{position:fixed;bottom:2rem;right:2rem;background:var(--card);border:1px solid var(--gold);color:var(--text);padding:.9rem 1.5rem;font-size:.82rem;z-index:9998;transform:translateY(80px);opacity:0;transition:all .4s ease;max-width:300px}
    #toast.show{transform:translateY(0);opacity:1}

    /* ── ANIMATIONS ── */
    @keyframes fadeUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
    .rev{opacity:0;transform:translateY(22px);transition:opacity .7s ease,transform .7s ease}
    .rev.on{opacity:1;transform:translateY(0)}
    .d1{transition-delay:.1s}.d2{transition-delay:.2s}.d3{transition-delay:.3s}.d4{transition-delay:.4s}

    /* ── FAQ ── */
    .faq-list{display:flex;flex-direction:column;gap:.8rem}
    .faq-item{background:var(--card);border:1px solid var(--border);border-radius:2px;overflow:hidden;transition:border-color .3s}
    .faq-item.open{border-color:rgba(200,168,75,.35)}
    .faq-q{display:flex;justify-content:space-between;align-items:center;padding:1.2rem 1.5rem;cursor:pointer;font-size:.9rem;font-weight:500;gap:1rem;transition:color .3s}
    .faq-q:hover{color:var(--gold)}
    .faq-item.open .faq-q{color:var(--gold)}
    .faq-icon{font-size:.85rem;color:var(--gold);flex-shrink:0;transition:transform .3s}
    .faq-item.open .faq-icon{transform:rotate(45deg)}
    .faq-a{max-height:0;overflow:hidden;transition:max-height .4s ease,padding .3s ease;font-size:.85rem;color:var(--muted);line-height:1.8;padding:0 1.5rem}
    .faq-item.open .faq-a{max-height:200px;padding:.2rem 1.5rem 1.2rem}

    @media(max-width:768px){
      .books-wrap{width:260px;height:340px}
      .bk-main{width:150px}.bk-l,.bk-r{width:100px}
      .hero-metrics{gap:1.5rem}
      .step-conn{display:none}
      .cart-sidebar{width:100vw}
      .pay-method{grid-template-columns:repeat(2,1fr)}
      .detail-btns{flex-direction:column}
    }
  </style>
</head>
<body>

<!-- ═══ TOP BAR ═══ -->
<div id="topBar" style="background:var(--gold);color:#08080f;text-align:center;padding:.5rem 1rem;font-size:.72rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;position:relative;z-index:1001">
  ✦ Promo Spesial — Diskon hingga 35% untuk koleksi pilihan! Harga mulai Rp 28.000. &nbsp;
  <a href="#katalog" style="color:#08080f;text-decoration:underline;font-weight:700">Temukan Sekarang →</a>
  &nbsp;&nbsp;
  <button onclick="document.getElementById('topBar').style.display='none'" style="background:transparent;border:none;cursor:pointer;font-size:.9rem;position:absolute;right:1rem;top:50%;transform:translateY(-50%);opacity:.6">✕</button>
</div>

<!-- ═══ NAVBAR ═══ -->
<nav class="navbar" id="navbar">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between w-100">
      <a href="#" class="nav-brand">Aksara<sup>store</sup></a>
      <div class="d-none d-lg-flex align-items-center gap-1">
        <a href="#cara-beli" class="nav-link-i">Cara Beli</a>
        <a href="#katalog" class="nav-link-i">Katalog</a>
        <a href="#keunggulan" class="nav-link-i">Keunggulan</a>
        <a href="#testimoni" class="nav-link-i">Testimoni</a>
        <a href="#kontak" class="nav-link-i">Kontak</a>
        <?php if ($isLoggedIn): ?>
          <a href="<?= $userRole === 'admin' ? 'admin/dashboard.php' : 'akun/dashboard.php' ?>" class="nav-link-i"><i class="bi bi-person-circle"></i> <?= htmlspecialchars(explode(' ', $userName)[0]) ?></a>
          <a href="auth/logout.php" class="nav-link-i"><i class="bi bi-box-arrow-right"></i></a>
        <?php else: ?>
          <a href="auth/login.php" class="nav-link-i"><i class="bi bi-person"></i> Masuk</a>
        <?php endif; ?>
        <button class="cart-btn ms-3" onclick="toggleCart()">
          <i class="bi bi-bag"></i>
          <span class="cart-count" id="cartCount">0</span>
        </button>
        <a href="#katalog" class="btn-cta ms-2">Beli Sekarang</a>
      </div>
      <div class="d-flex d-lg-none align-items-center gap-2">
        <a href="<?= $isLoggedIn ? ($userRole === 'admin' ? 'admin/dashboard.php' : 'akun/dashboard.php') : 'auth/login.php' ?>" class="cart-btn"><i class="bi bi-person"></i></a>
        <button class="cart-btn" onclick="toggleCart()">
          <i class="bi bi-bag"></i>
          <span class="cart-count" id="cartCountMobile">0</span>
        </button>
        <button class="btn p-0 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mNav">
          <i class="bi bi-list text-warning fs-3"></i>
        </button>
      </div>
    </div>
    <div class="collapse d-lg-none mt-3" id="mNav">
      <div class="d-flex flex-column gap-2 pb-3" style="border-top:1px solid var(--border);padding-top:1rem">
        <a href="#cara-beli" class="nav-link-i">Cara Beli</a>
        <a href="#katalog" class="nav-link-i">Katalog</a>
        <a href="#keunggulan" class="nav-link-i">Keunggulan</a>
        <a href="#testimoni" class="nav-link-i">Testimoni</a>
        <a href="#faq" class="nav-link-i">FAQ</a>
        <a href="#kontak" class="nav-link-i">Kontak</a>
        <?php if ($isLoggedIn): ?>
          <a href="<?= $userRole === 'admin' ? 'admin/dashboard.php' : 'akun/dashboard.php' ?>" class="nav-link-i"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($userName) ?></a>
          <a href="auth/logout.php" class="nav-link-i"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
        <?php else: ?>
          <a href="auth/login.php" class="nav-link-i"><i class="bi bi-person me-1"></i>Masuk / Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- ═══ HERO ═══ -->
<section class="hero" id="beranda">
  <div class="hero-ambient"></div>
  <div class="hero-grid"></div>
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="eyebrow"><span class="dot-pulse"></span>Platform Novel Digital Terpercaya Indonesia</div>
        <h1 class="hero-h1">Baca Novel<br/><span class="it">Favorit</span> Kini<br/><span class="ol">Lebih Mudah</span></h1>
        <p class="hero-sub">Nikmati pengalaman membaca novel digital kapan saja dan di mana saja hanya melalui satu platform. Tidak perlu nunggu pengiriman atau bawa buku tebal ke mana-mana.</p>
        <div class="hero-aida-points" style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.8rem">
          <div style="display:flex;align-items:center;gap:.6rem;font-size:.8rem;color:var(--text)"><span style="color:var(--gold);font-size:.9rem">✦</span> Berbagai genre: Romance, Fantasy, Mystery, hingga Thriller</div>
          <div style="display:flex;align-items:center;gap:.6rem;font-size:.8rem;color:var(--text)"><span style="color:var(--gold);font-size:.9rem">✦</span> Harga terjangkau mulai Rp 28.000 — lebih hemat dari buku fisik</div>
          <div style="display:flex;align-items:center;gap:.6rem;font-size:.8rem;color:var(--text)"><span style="color:var(--gold);font-size:.9rem">✦</span> Akses instan — cukup download dan langsung baca dari HP atau laptop</div>
        </div>
        <div class="hero-btns d-flex flex-wrap gap-3">
          <a href="#katalog" class="btn-gold"><i class="bi bi-book me-2"></i>Temukan Ceritamu Sekarang</a>
          <a href="#cara-beli" class="btn-ghost"><i class="bi bi-play-circle me-2"></i>Cara Beli</a>
        </div>
        <div class="hero-metrics">
          <div><div class="mval">500+</div><div class="mlbl">Judul Novel</div></div>
          <div><div class="mval">12K+</div><div class="mlbl">Pembaca</div></div>
          <div><div class="mval">4.9★</div><div class="mlbl">Rating</div></div>
          <div><div class="mval">50+</div><div class="mlbl">Penulis</div></div>
        </div>
      </div>
      <div class="col-lg-6 d-flex justify-content-center">
        <div style="position:relative">
          <div class="books-wrap">
            <div class="bk bk-l">
              <div class="bk-face" style="background:linear-gradient(160deg,#0a1628,#1a3a5c)">
                <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 40% 30%,rgba(100,180,255,.1),transparent)"></div>
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,rgba(126,200,227,.4),transparent)"></div>
                <div style="font-size:.45rem;letter-spacing:2px;text-transform:uppercase;color:rgba(126,200,227,.5);margin-bottom:.4rem;position:relative;z-index:1">NOVEL FANTASY</div>
                <div class="bk-ico">⚔️</div>
                <div class="bk-ttl" style="color:#7ec8e3">Negeri di Atas Awan</div>
                <div style="width:30px;height:1px;background:rgba(126,200,227,.3);margin:.4rem auto;position:relative;z-index:1"></div>
                <div class="bk-aut" style="color:#7ec8e3">Sekar Ayu</div>
                <div style="position:absolute;bottom:.6rem;left:0;right:0;text-align:center;font-size:.42rem;letter-spacing:1.5px;color:rgba(126,200,227,.35);text-transform:uppercase">Aksara Store</div>
              </div>
            </div>
            <div class="bk bk-main">
              <div class="bk-face" style="background:linear-gradient(160deg,#1a0c2e,#3d1560,#2d1040)">
                <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(200,168,75,.12),transparent)"></div>
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,rgba(200,168,75,.5),transparent)"></div>
                <div style="position:absolute;top:.7rem;left:0;right:0;text-align:center;font-size:.45rem;letter-spacing:2px;text-transform:uppercase;color:rgba(200,168,75,.6);z-index:1">NOVEL ROMANCE</div>
                <div class="bk-ico" style="font-size:2.8rem;margin-top:.4rem">🌹</div>
                <div class="bk-ttl" style="color:var(--gold);font-size:1.05rem">Satu Musim<br/>Bersamamu</div>
                <div style="width:35px;height:1px;background:rgba(200,168,75,.4);margin:.5rem auto;position:relative;z-index:1"></div>
                <div class="bk-aut" style="color:var(--gold)">Dira Kusuma</div>
                <div style="position:absolute;bottom:.6rem;right:.7rem;font-size:.48rem;color:var(--gold);border:1px solid rgba(200,168,75,.3);padding:.15rem .4rem;letter-spacing:1px;z-index:1">EBOOK</div>
                <div style="position:absolute;bottom:.6rem;left:.7rem;font-size:.42rem;letter-spacing:1px;color:rgba(200,168,75,.4);z-index:1">PDF · EPUB</div>
              </div>
            </div>
            <div class="bk bk-r">
              <div class="bk-face" style="background:linear-gradient(160deg,#0a0a0a,#1a1a1a,#2d1b00)">
                <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 60% 40%,rgba(255,120,0,.08),transparent)"></div>
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,rgba(255,140,0,.4),transparent)"></div>
                <div style="font-size:.45rem;letter-spacing:2px;text-transform:uppercase;color:rgba(255,140,0,.5);margin-bottom:.4rem;position:relative;z-index:1">THRILLER</div>
                <div class="bk-ico">🔪</div>
                <div class="bk-ttl" style="color:#ff8c00">Bayangan<br/>Tak Bernama</div>
                <div style="width:30px;height:1px;background:rgba(255,140,0,.3);margin:.4rem auto;position:relative;z-index:1"></div>
                <div class="bk-aut" style="color:#ff8c00">Rian Maulana</div>
                <div style="position:absolute;bottom:.6rem;left:0;right:0;text-align:center;font-size:.42rem;letter-spacing:1.5px;color:rgba(255,140,0,.35);text-transform:uppercase">Aksara Store</div>
              </div>
            </div>
            <div class="fl-badge">✦ Terlaris</div>
            <div class="fl-badge2">
              <div style="font-size:.58rem;color:var(--gold);letter-spacing:1.5px;text-transform:uppercase">Akses Instan</div>
              <div style="font-size:.7rem;margin-top:.1rem">📲 Setelah Pembayaran</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TICKER -->
<div class="ticker">
  <div class="ticker-inner">
    <span>✦ ROMANCE</span><span>✦ THRILLER</span><span>✦ FANTASY</span><span>✦ MISTERI</span><span>✦ DRAMA</span><span>✦ HORROR</span><span>✦ AKSES INSTAN</span><span>✦ HARGA MULAI RP 28.000</span><span>✦ FILE KE EMAIL DALAM 10 MENIT</span><span>✦ BACA DI HP & LAPTOP</span>
    <span>✦ ROMANCE</span><span>✦ THRILLER</span><span>✦ FANTASY</span><span>✦ MISTERI</span><span>✦ DRAMA</span><span>✦ HORROR</span><span>✦ AKSES INSTAN</span><span>✦ HARGA MULAI RP 28.000</span><span>✦ FILE KE EMAIL DALAM 10 MENIT</span><span>✦ BACA DI HP & LAPTOP</span>
  </div>
</div>

<!-- ═══ CARA BELI ═══ -->
<section class="section section-alt" id="cara-beli">
  <div class="container">
    <div class="text-center mb-5 rev">
      <div class="tag-line mx-auto" style="justify-content:center">Mudah & Cepat</div>
      <h2 class="sec-h">3 Langkah Mudah, <em>Langsung Baca</em></h2>
      <div class="divider mx-auto"></div>
      <p style="color:var(--muted);font-size:.85rem;margin-top:.8rem">Proses pembelian tidak sampai 10 menit. Selesai bayar, ebook langsung di inbox kamu.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3 rev d1"><div class="position-relative">
        <div class="step-num">01</div><div class="step-icon"><i class="bi bi-search"></i></div>
        <h5 class="step-title">Pilih Novel</h5><p class="step-desc">Jelajahi berbagai genre — Romance, Fantasy, Mystery, Thriller, Drama, hingga Horror. Gunakan filter atau pencarian untuk menemukan novel favoritmu.</p>
        <div class="step-conn d-none d-lg-block"></div>
      </div></div>
      <div class="col-md-6 col-lg-3 rev d2"><div class="position-relative">
        <div class="step-num">02</div><div class="step-icon"><i class="bi bi-cart-plus"></i></div>
        <h5 class="step-title">Tambah ke Keranjang</h5><p class="step-desc">Klik "Temukan Ceritamu" untuk menambahkan novel ke keranjang. Bisa beli lebih dari satu judul sekaligus dalam satu transaksi.</p>
        <div class="step-conn d-none d-lg-block"></div>
      </div></div>
      <div class="col-md-6 col-lg-3 rev d3"><div class="position-relative">
        <div class="step-num">03</div><div class="step-icon"><i class="bi bi-credit-card"></i></div>
        <h5 class="step-title">Bayar Sekali</h5><p class="step-desc">Tidak ada biaya langganan. Pilih metode pembayaran — Transfer Bank, QRIS, GoPay, OVO, Dana, atau ShopeePay. Semua transaksi dilindungi enkripsi SSL.</p>
        <div class="step-conn d-none d-lg-block"></div>
      </div></div>
      <div class="col-md-6 col-lg-3 rev d4"><div class="position-relative">
        <div class="step-num">04</div><div class="step-icon"><i class="bi bi-download"></i></div>
        <h5 class="step-title">Download & Baca</h5><p class="step-desc">File PDF & EPUB langsung dikirim ke emailmu dalam 5–10 menit. Download dan baca kapan saja di HP, tablet, atau laptop — tanpa aplikasi khusus.</p>
      </div></div>
    </div>
  </div>
</section>

<!-- ═══ KATALOG ═══ -->
<section class="section" id="katalog">
  <div class="container">
    <div class="row align-items-end mb-4">
      <div class="col-lg-6 rev">
        <div class="tag-line">Koleksi Pilihan</div>
        <h2 class="sec-h">Novel yang Bikin Kamu <em>Susah Berhenti</em></h2>
        <div class="divider"></div>
        <p style="color:var(--muted);font-size:.85rem;margin-top:.5rem">Setiap judul dikurasi. Tersedia berbagai genre menarik dengan harga terjangkau dan akses instan setelah pembelian.</p>
      </div>
    </div>

    <!-- Search -->
    <div class="search-wrap rev">
      <i class="bi bi-search"></i>
      <input type="text" id="searchInput" placeholder="Cari judul novel, pengarang, atau genre..." oninput="handleSearch(this)"/>
      <button class="clear-btn" id="clearBtn" onclick="clearSearch()"><i class="bi bi-x-circle"></i> Hapus</button>
    </div>

    <!-- Filter + Sort -->
    <div class="filter-row rev">
      <button class="pill active" onclick="filterG('semua',this)">Semua</button>
      <button class="pill" onclick="filterG('romance',this)">Romance</button>
      <button class="pill" onclick="filterG('thriller',this)">Thriller</button>
      <button class="pill" onclick="filterG('fantasy',this)">Fantasy</button>
      <button class="pill" onclick="filterG('misteri',this)">Misteri</button>
      <button class="pill" onclick="filterG('drama',this)">Drama</button>
      <button class="pill" onclick="filterG('horror',this)">Horror</button>
      <select class="sort-select ms-auto" id="sortSelect" onchange="sortBooks()">
        <option value="default">Urutkan: Default</option>
        <option value="price-asc">Harga: Terendah</option>
        <option value="price-desc">Harga: Tertinggi</option>
        <option value="name-asc">Nama: A-Z</option>
        <option value="rating-desc">Rating Tertinggi</option>
      </select>
      <span class="result-count" id="resultCount">13 novel</span>
    </div>

    <div class="row g-4" id="grid"></div>
    <div id="noResults" class="no-results" style="display:none">
      <i class="bi bi-search"></i>
      <div style="font-family:var(--serif);font-size:1.2rem;margin-bottom:.5rem">Novel tidak ditemukan</div>
      <div style="font-size:.85rem">Coba kata kunci atau genre yang berbeda</div>
    </div>
  </div>
</section>

<!-- ═══ KEUNGGULAN ═══ -->
<section class="section section-alt" id="keunggulan">
  <div class="container">
    <div class="text-center mb-5 rev">
      <div class="tag-line mx-auto" style="justify-content:center">Kenapa Aksara Store</div>
      <h2 class="sec-h">Baca Lebih Banyak, <em>Bayar Lebih Sedikit</em></h2>
      <div class="divider mx-auto"></div>
      <p style="color:var(--muted);font-size:.85rem;margin-top:.8rem">Inilah alasan ribuan pembaca sudah beralih ke cara membaca yang lebih modern dan praktis.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 rev d1"><div class="feat-card"><span class="feat-ico"><i class="bi bi-lightning-charge"></i></span><h5 class="feat-title">Akses Instan</h5><p class="feat-desc">Selesai bayar, file langsung ada. Tidak perlu nunggu kurir atau approval manual. Ebook tersedia untuk diunduh begitu pembayaran dikonfirmasi.</p></div></div>
      <div class="col-md-6 col-lg-4 rev d2"><div class="feat-card"><span class="feat-ico"><i class="bi bi-phone"></i></span><h5 class="feat-title">Baca di Semua Perangkat</h5><p class="feat-desc">Satu beli, selamanya milikmu. Format PDF & EPUB kompatibel dengan HP, tablet, laptop, dan e-reader. Buka pagi di HP, lanjut malam di laptop.</p></div></div>
      <div class="col-md-6 col-lg-4 rev d3"><div class="feat-card"><span class="feat-ico"><i class="bi bi-shield-check"></i></span><h5 class="feat-title">Transaksi Aman</h5><p class="feat-desc">Enkripsi SSL di setiap transaksi. Data kamu aman, file pasti sampai. Berbagai metode pembayaran tersedia dan teruji keamanannya.</p></div></div>
      <div class="col-md-6 col-lg-4 rev d1"><div class="feat-card"><span class="feat-ico"><i class="bi bi-wallet2"></i></span><h5 class="feat-title">Harga Terjangkau</h5><p class="feat-desc">Novel premium mulai Rp 28.000 — separuh harga toko buku, kualitas yang sama. Bacaan berkualitas dengan harga yang ramah di kantong.</p></div></div>
      <div class="col-md-6 col-lg-4 rev d2"><div class="feat-card"><span class="feat-ico"><i class="bi bi-heart"></i></span><h5 class="feat-title">Dukung Penulis Lokal</h5><p class="feat-desc">Belimu = royalti langsung ke penulis. Sebagian besar hasil penjualan diterima langsung penulis. Bentuk apresiasi yang paling nyata.</p></div></div>
      <div class="col-md-6 col-lg-4 rev d3"><div class="feat-card"><span class="feat-ico"><i class="bi bi-headset"></i></span><h5 class="feat-title">Dukungan 7×24 Jam</h5><p class="feat-desc">Ada masalah? Chat kami kapan saja. Tim kami siap membantu melalui WhatsApp dan email — dijawab langsung oleh tim, bukan bot.</p></div></div>
    </div>
  </div>
</section>

<!-- ═══ TESTIMONI ═══ -->
<section class="section" id="testimoni">
  <div class="container">
    <div class="text-center mb-5 rev">
      <div class="tag-line mx-auto" style="justify-content:center">Bukan Janji Kami</div>
      <h2 class="sec-h">Ini Kata <em>Mereka</em></h2>
      <div class="divider mx-auto"></div>
      <p style="color:var(--muted);font-size:.85rem;margin-top:.8rem">Lebih dari 12.000 pembaca sudah puas. Rata-rata rating 4.9/5 dari 500+ ulasan masuk.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 rev d1"><div class="t-card"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text">Koleksinya lengkap banget dan harga lebih murah dari beli buku fisik. Sudah beli lebih dari 10 novel di sini, semuanya langsung bisa didownload setelah bayar. Tidak ribet sama sekali. Highly recommended!</p><div class="d-flex align-items-center"><div class="t-av">A</div><div><div class="t-name">Anisa Putri</div><div class="t-role">Pembaca Aktif · Jakarta</div></div></div></div></div>
      <div class="col-md-6 col-lg-4 rev d2"><div class="t-card"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text">Sebagai mahasiswa, harganya sangat bersahabat di kantong. Bisa dibaca di HP tanpa aplikasi khusus, jadi praktis banget dibawa ke mana-mana. Genre-nya juga beragam, cocok untuk semua selera.</p><div class="d-flex align-items-center"><div class="t-av">R</div><div><div class="t-name">Rizky Firmansyah</div><div class="t-role">Mahasiswa · Yogyakarta</div></div></div></div></div>
      <div class="col-md-6 col-lg-4 rev d3"><div class="t-card"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text">Tampilan webnya keren dan proses belinya simpel — tidak sampai 5 menit sudah bisa baca! Sekarang saya sudah jadi langganan tetap. Senang banget ada platform ebook novel berkualitas kayak gini.</p><div class="d-flex align-items-center"><div class="t-av">S</div><div><div class="t-name">Sinta Maharani</div><div class="t-role">Guru · Bandung</div></div></div></div></div>
      <div class="col-md-6 col-lg-4 rev d1"><div class="t-card"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text">Saya suka banget karena bisa beli beberapa novel sekaligus dalam satu keranjang. Harga per novel jauh lebih hemat dibanding beli fisik. File-nya juga rapi dan langsung bisa dibuka di tablet saya.</p><div class="d-flex align-items-center"><div class="t-av">D</div><div><div class="t-name">Dewi Lestari</div><div class="t-role">Ibu Rumah Tangga · Surabaya</div></div></div></div></div>
      <div class="col-md-6 col-lg-4 rev d2"><div class="t-card"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text">Koleksi genre thriller-nya oke banget! Saya sudah baca hampir semua thriller yang ada. Customer service-nya juga responsif waktu saya ada pertanyaan soal format file. Top banget!</p><div class="d-flex align-items-center"><div class="t-av">B</div><div><div class="t-name">Bagas Pratama</div><div class="t-role">Pekerja Kantoran · Medan</div></div></div></div></div>
      <div class="col-md-6 col-lg-4 rev d3"><div class="t-card"><div class="t-q">"</div><div class="t-stars">★★★★☆</div><p class="t-text">Sudah 3 bulan jadi pelanggan dan belum pernah kecewa. Pilihan novelnya terus bertambah, dan notifikasi email-nya membantu banget untuk tahu novel baru. Harga promo-nya juga sering ada!</p><div class="d-flex align-items-center"><div class="t-av">N</div><div><div class="t-name">Nadia Rahayu</div><div class="t-role">Mahasiswi · Semarang</div></div></div></div></div>
    </div>
    <!-- Rating summary -->
    <div class="rev mt-5 text-center">
      <div style="display:inline-flex;align-items:center;gap:2rem;background:var(--card);border:1px solid var(--border);padding:1.2rem 2.5rem;border-radius:2px;flex-wrap:wrap;justify-content:center;gap:1.5rem">
        <div style="text-align:center"><div style="font-family:var(--serif);font-size:2.5rem;font-weight:700;color:var(--gold);line-height:1">4.9</div><div style="color:var(--gold);font-size:.85rem">★★★★★</div><div style="font-size:.68rem;color:var(--muted);letter-spacing:1.5px;text-transform:uppercase;margin-top:.2rem">Rata-rata Rating</div></div>
        <div style="width:1px;height:50px;background:var(--border)"></div>
        <div style="text-align:center"><div style="font-family:var(--serif);font-size:2.5rem;font-weight:700;color:var(--gold);line-height:1">12K+</div><div style="font-size:.68rem;color:var(--muted);letter-spacing:1.5px;text-transform:uppercase;margin-top:.4rem">Pembaca Aktif</div></div>
        <div style="width:1px;height:50px;background:var(--border)"></div>
        <div style="text-align:center"><div style="font-family:var(--serif);font-size:2.5rem;font-weight:700;color:var(--gold);line-height:1">98%</div><div style="font-size:.68rem;color:var(--muted);letter-spacing:1.5px;text-transform:uppercase;margin-top:.4rem">Kepuasan Pelanggan</div></div>
        <div style="width:1px;height:50px;background:var(--border)"></div>
        <div style="text-align:center"><div style="font-family:var(--serif);font-size:2.5rem;font-weight:700;color:var(--gold);line-height:1">500+</div><div style="font-size:.68rem;color:var(--muted);letter-spacing:1.5px;text-transform:uppercase;margin-top:.4rem">Ulasan Masuk</div></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ FAQ ═══ -->
<section class="section section-alt" id="faq">
  <div class="container">
    <div class="text-center mb-5 rev">
      <div class="tag-line mx-auto" style="justify-content:center">Pertanyaan Umum</div>
      <h2 class="sec-h">Masih Ada <em>Pertanyaan?</em></h2>
      <div class="divider mx-auto"></div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="faq-list rev">
          <div class="faq-item">
            <div class="faq-q" onclick="toggleFaq(this)">
              <span>Dalam format apa ebook dikirimkan?</span>
              <i class="bi bi-plus-lg faq-icon"></i>
            </div>
            <div class="faq-a">Ebook dikirim dalam format PDF dan EPUB yang bisa dibaca di semua perangkat — smartphone, tablet, laptop, maupun e-reader tanpa aplikasi khusus.</div>
          </div>
          <div class="faq-item">
            <div class="faq-q" onclick="toggleFaq(this)">
              <span>Berapa lama file dikirim setelah pembayaran?</span>
              <i class="bi bi-plus-lg faq-icon"></i>
            </div>
            <div class="faq-a">File langsung dikirim ke email kamu dalam 5–10 menit setelah pembayaran dikonfirmasi. Cek juga folder Promosi atau Spam jika tidak ada di inbox.</div>
          </div>
          <div class="faq-item">
            <div class="faq-q" onclick="toggleFaq(this)">
              <span>Apakah bisa dibaca di lebih dari satu perangkat?</span>
              <i class="bi bi-plus-lg faq-icon"></i>
            </div>
            <div class="faq-a">Ya! Satu pembelian bisa kamu simpan dan baca di semua perangkat yang kamu miliki. Tidak ada batasan jumlah perangkat.</div>
          </div>
          <div class="faq-item">
            <div class="faq-q" onclick="toggleFaq(this)">
              <span>Apakah ada biaya langganan bulanan?</span>
              <i class="bi bi-plus-lg faq-icon"></i>
            </div>
            <div class="faq-a">Tidak ada. Kamu hanya bayar untuk novel yang kamu beli — sekali bayar, buku jadi milikmu selamanya. Tidak ada biaya tersembunyi atau langganan wajib.</div>
          </div>
          <div class="faq-item">
            <div class="faq-q" onclick="toggleFaq(this)">
              <span>Bagaimana jika file tidak bisa dibuka atau tidak sampai?</span>
              <i class="bi bi-plus-lg faq-icon"></i>
            </div>
            <div class="faq-a">Hubungi tim kami melalui WhatsApp atau email. Kami siap membantu 7×24 jam dan akan mengirimkan ulang file kamu tanpa biaya tambahan — garansi resend file.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ CTA BAND ═══ -->
<div class="cta-band">
  <div class="container text-center rev">
    <div class="tag-line mx-auto" style="justify-content:center">Yuk, Mulai Sekarang</div>
    <h2 class="cta-h mb-3">Temukan Cerita Favoritmu<br/><em>dan Rasakan Pengalaman Baru!</em></h2>
    <p style="color:var(--muted);font-size:.9rem;max-width:500px;margin:.5rem auto 2rem;line-height:1.8">Rasakan pengalaman membaca yang lebih modern, praktis, dan menyenangkan. Bergabung dengan <strong style="color:var(--gold)">12.000+ pembaca</strong> yang sudah merasakan manfaatnya — hanya di Aksara Store.</p>
    <div class="d-flex flex-wrap gap-3 justify-content-center">
      <a href="#katalog" class="btn-gold"><i class="bi bi-book me-2"></i>Temukan Ceritamu Sekarang</a>
      <a href="#kontak" class="btn-ghost"><i class="bi bi-chat me-2"></i>Ada Pertanyaan?</a>
    </div>
    <div style="margin-top:2rem;display:flex;justify-content:center;gap:2.5rem;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--muted)"><i class="bi bi-shield-check" style="color:var(--gold)"></i> Transaksi Aman</div>
      <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--muted)"><i class="bi bi-lightning-charge" style="color:var(--gold)"></i> Akses Instan</div>
      <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--muted)"><i class="bi bi-headset" style="color:var(--gold)"></i> Support 24/7</div>
      <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:var(--muted)"><i class="bi bi-arrow-counterclockwise" style="color:var(--gold)"></i> Garansi Resend File</div>
    </div>
  </div>
</div>

<!-- ═══ KONTAK ═══ -->
<section class="section section-alt" id="kontak">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-5 rev">
        <div class="tag-line">Hubungi Kami</div>
        <h2 class="sec-h">Ada <em>Pertanyaan?</em></h2>
        <div class="divider"></div>
        <p style="color:var(--muted);font-size:.88rem;line-height:1.8;margin-bottom:2rem">Tim kami siap membantu 7 hari seminggu melalui berbagai channel.</p>
        <div class="d-flex align-items-center gap-3 mb-3"><div class="c-icon"><i class="bi bi-whatsapp"></i></div><div><div style="font-size:.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted)">WhatsApp</div><div style="font-size:.9rem">+62 812-3456-7890</div></div></div>
        <div class="d-flex align-items-center gap-3 mb-3"><div class="c-icon"><i class="bi bi-envelope"></i></div><div><div style="font-size:.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted)">Email</div><div style="font-size:.9rem">halo@aksarastore.id</div></div></div>
        <div class="d-flex align-items-center gap-3 mb-4"><div class="c-icon"><i class="bi bi-instagram"></i></div><div><div style="font-size:.65rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted)">Instagram</div><div style="font-size:.9rem">@aksarastore.id</div></div></div>
        <div><a href="#" class="soc"><i class="bi bi-instagram"></i></a><a href="#" class="soc"><i class="bi bi-twitter-x"></i></a><a href="#" class="soc"><i class="bi bi-tiktok"></i></a><a href="#" class="soc"><i class="bi bi-whatsapp"></i></a></div>
      </div>
      <div class="col-lg-7 rev d1">
        <div style="background:var(--card);border:1px solid var(--border);padding:2.5rem;border-radius:2px">
          <h5 style="font-family:var(--serif);font-size:1.3rem;margin-bottom:1.5rem;color:var(--gold)">Kirim Pesan</h5>
          <div class="row g-3">
            <div class="col-md-6"><label class="f-label">Nama</label><input type="text" class="c-input form-control" placeholder="Nama kamu..."/></div>
            <div class="col-md-6"><label class="f-label">Email</label><input type="email" class="c-input form-control" placeholder="email@kamu.com"/></div>
            <div class="col-12"><label class="f-label">Topik</label><select class="c-input form-select"><option>Pertanyaan Pembelian</option><option>Masalah File</option><option>Kerjasama Penulis</option><option>Lainnya</option></select></div>
            <div class="col-12"><label class="f-label">Pesan</label><textarea class="c-input form-control" rows="4" placeholder="Tulis pesanmu..."></textarea></div>
            <div class="col-12"><button class="btn-gold w-100" onclick="toast('✦ Pesan berhasil dikirim!')"><i class="bi bi-send me-2"></i>Kirim Pesan</button></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer>
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4"><div class="f-brand">Aksara Store</div><p class="f-desc">Platform toko ebook novel digital terpercaya Indonesia. Temukan berbagai genre novel terbaik, baca kapan saja dan di mana saja dengan harga terjangkau.</p><div class="mt-3"><a href="#" class="soc"><i class="bi bi-instagram"></i></a><a href="#" class="soc"><i class="bi bi-twitter-x"></i></a><a href="#" class="soc"><i class="bi bi-tiktok"></i></a></div></div>
      <div class="col-6 col-lg-2"><div class="f-head">Menu</div><a href="#beranda" class="f-link">Beranda</a><a href="#cara-beli" class="f-link">Cara Beli</a><a href="#katalog" class="f-link">Katalog</a><a href="#keunggulan" class="f-link">Keunggulan</a><a href="#testimoni" class="f-link">Testimoni</a><a href="#faq" class="f-link">FAQ</a><a href="#kontak" class="f-link">Kontak</a></div>
      <div class="col-6 col-lg-2"><div class="f-head">Genre</div><a href="#" class="f-link">Romance</a><a href="#" class="f-link">Thriller</a><a href="#" class="f-link">Fantasy</a><a href="#" class="f-link">Misteri</a><a href="#" class="f-link">Drama</a><a href="#" class="f-link">Horror</a></div>
      <div class="col-lg-4"><div class="f-head">Newsletter</div><p style="color:var(--muted);font-size:.8rem;margin-bottom:1rem;line-height:1.75">Dapatkan info novel terbaru dan promo eksklusif langsung di emailmu.</p><div class="input-group"><input type="email" class="c-input form-control" placeholder="Email kamu..."/><button class="btn-gold px-3" onclick="toast('✦ Berhasil berlangganan!')" style="border-radius:0;font-size:.8rem"><i class="bi bi-arrow-right"></i></button></div></div>
    </div>
    <div class="f-bottom d-flex justify-content-between flex-wrap gap-2">
      <span>© 2025 Aksara Store. Seluruh hak dilindungi.</span>
      <span style="color:var(--gold)">Dibuat dengan ♥ untuk para pecinta buku</span>
    </div>
  </div>
</footer>

<!-- ═══ CART SIDEBAR ═══ -->
<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-sidebar" id="cartSidebar">
  <div class="cart-header">
    <div class="cart-title">Keranjang <em style="font-style:italic;color:var(--gold)">Belanja</em></div>
    <button class="cart-close" onclick="toggleCart()"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="cart-body" id="cartBody">
    <div class="cart-empty" id="cartEmpty">
      <i class="bi bi-bag-x"></i>
      <div style="font-family:var(--serif);font-size:1.1rem;margin-bottom:.5rem">Keranjang kosong</div>
      <div style="font-size:.82rem">Tambahkan novel untuk mulai belanja</div>
    </div>
    <div id="cartItems"></div>
  </div>
  <div class="cart-footer" id="cartFooter" style="display:none">
    <div class="cart-total-row"><span>Subtotal</span><span id="cartSubtotal">Rp 0</span></div>
    <div class="cart-total-row"><span>Diskon</span><span id="cartDisc" style="color:#6dbf6d">- Rp 0</span></div>
    <div class="cart-grand"><span>Total</span><span id="cartTotal">Rp 0</span></div>
    <button class="btn-gold w-100" onclick="openCheckout()"><i class="bi bi-credit-card me-2"></i>Lanjut ke Checkout</button>
    <button class="btn-ghost w-100 mt-2" style="font-size:.75rem" onclick="clearCart()">Kosongkan Keranjang</button>
  </div>
</div>

<!-- ═══ CHECKOUT MODAL ═══ -->
<div class="modal-overlay" id="checkoutOverlay">
  <div class="modal-box" id="checkoutBox">
    <div class="modal-head">
      <div class="modal-title">Checkout</div>
      <button class="modal-close" onclick="closeCheckout()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <!-- Step Indicator -->
      <div class="step-indicator">
        <div class="si-step"><div class="si-num active" id="si1">1</div><div class="si-label active" id="sl1">Data Diri</div></div>
        <div class="si-step"><div class="si-num" id="si2">2</div><div class="si-label" id="sl2">Pembayaran</div></div>
        <div class="si-step"><div class="si-num" id="si3">3</div><div class="si-label" id="sl3">Konfirmasi</div></div>
      </div>

      <!-- Step 1 -->
      <div class="form-step active" id="step1">
        <div class="row g-3">
          <div class="col-md-6"><label class="f-label">Nama Lengkap *</label><input class="f-inp" type="text" id="co-name" placeholder="Nama lengkap kamu" value="<?= $isLoggedIn ? htmlspecialchars($userName) : '' ?>"/></div>
          <div class="col-md-6"><label class="f-label">Email *</label><input class="f-inp" type="email" id="co-email" placeholder="Untuk pengiriman ebook" value="<?= $isLoggedIn ? htmlspecialchars($_SESSION['email']) : '' ?>"/></div>
          <div class="col-md-6"><label class="f-label">No. WhatsApp</label><input class="f-inp" type="text" id="co-phone" placeholder="08xx-xxxx-xxxx"/></div>
          <div class="col-md-6"><label class="f-label">Kota</label><input class="f-inp" type="text" id="co-city" placeholder="Kota domisili"/></div>
          <div class="col-12 mt-3">
            <button class="btn-gold w-100" onclick="goStep(2)"><i class="bi bi-arrow-right me-2"></i>Lanjut ke Pembayaran</button>
          </div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="form-step" id="step2">
        <label class="f-label mb-2">Pilih Metode Pembayaran *</label>
        <div class="pay-method">
          <div class="pay-opt" onclick="selectPay(this,'transfer')"><span class="pay-ico">🏦</span><span class="pay-name">Transfer Bank</span></div>
          <div class="pay-opt" onclick="selectPay(this,'qris')"><span class="pay-ico">📱</span><span class="pay-name">QRIS</span></div>
          <div class="pay-opt" onclick="selectPay(this,'gopay')"><span class="pay-ico">💚</span><span class="pay-name">GoPay</span></div>
          <div class="pay-opt" onclick="selectPay(this,'ovo')"><span class="pay-ico">💜</span><span class="pay-name">OVO</span></div>
          <div class="pay-opt" onclick="selectPay(this,'dana')"><span class="pay-ico">💙</span><span class="pay-name">Dana</span></div>
          <div class="pay-opt" onclick="selectPay(this,'shopee')"><span class="pay-ico">🧡</span><span class="pay-name">ShopeePay</span></div>
        </div>
        <div class="d-flex gap-2 mt-4">
          <button class="btn-ghost flex-1" onclick="goStep(1)" style="flex:1"><i class="bi bi-arrow-left me-1"></i>Kembali</button>
          <button class="btn-gold" onclick="goStep(3)" style="flex:2"><i class="bi bi-arrow-right me-2"></i>Konfirmasi</button>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="form-step" id="step3">
        <div style="background:var(--bg3);border:1px solid var(--border);padding:1.5rem;border-radius:2px;margin-bottom:1.5rem">
          <div style="font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:1rem">Ringkasan Pesanan</div>
          <div id="orderSummary"></div>
          <div class="order-total-row"><span>Total Pembayaran</span><span id="orderTotal">Rp 0</span></div>
        </div>
        <div style="background:var(--bg3);border:1px solid var(--border);padding:1.2rem;border-radius:2px;margin-bottom:1.5rem;font-size:.85rem">
          <div style="color:var(--muted);margin-bottom:.3rem;font-size:.68rem;letter-spacing:1.5px;text-transform:uppercase">Data Penerima</div>
          <div id="buyerSummary" style="color:var(--text)"></div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn-ghost" onclick="goStep(2)" style="flex:1"><i class="bi bi-arrow-left me-1"></i>Kembali</button>
          <button class="btn-gold" onclick="doCheckout()" style="flex:2"><i class="bi bi-bag-check me-2"></i>Bayar Sekarang</button>
        </div>
      </div>

      <!-- Success -->
      <div class="success-screen" id="successScreen">
        <div class="success-ico">🎉</div>
        <h3 style="font-family:var(--serif);font-size:1.8rem;margin-bottom:.5rem">Pesanan <em style="color:var(--gold);font-style:italic">Berhasil!</em></h3>
        <p style="color:var(--muted);font-size:.88rem;line-height:1.8;max-width:340px;margin:0 auto 1.5rem">Ebook akan dikirimkan ke email kamu dalam 5–10 menit. Cek folder inbox atau spam.</p>
        <div style="background:var(--bg3);border:1px solid var(--border);padding:1rem;border-radius:2px;font-size:.82rem;color:var(--muted);margin-bottom:1.5rem">
          <i class="bi bi-envelope-check" style="color:var(--gold);margin-right:.5rem"></i>
          File dikirim ke: <strong id="successEmail" style="color:var(--text)"></strong>
        </div>
        <button class="btn-gold" onclick="closeCheckout()">Kembali ke Toko</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ DETAIL MODAL ═══ -->
<div class="detail-overlay" id="detailOverlay">
  <div class="detail-box">
    <div class="detail-cover" id="detailCover"></div>
    <div class="detail-content">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="detail-genre" id="detailGenre"></div>
        <button class="modal-close" onclick="closeDetail()"><i class="bi bi-x-lg"></i></button>
      </div>
      <h2 class="detail-title" id="detailTitle"></h2>
      <div class="detail-author" id="detailAuthor"></div>
      <div class="detail-rating" id="detailRating"></div>
      <p class="detail-desc" id="detailDesc"></p>
      <div class="detail-meta" id="detailMeta"></div>
      <div class="detail-price-row">
        <div class="detail-price" id="detailPrice"></div>
        <div class="detail-orig" id="detailOrig"></div>
      </div>
      <div class="detail-btns">
        <button class="btn-detail-buy" id="detailBuyBtn"><i class="bi bi-bag-plus me-2"></i>Tambah ke Keranjang</button>
        <button class="btn-detail-wish"><i class="bi bi-heart"></i></button>
      </div>
    </div>
  </div>
</div>

<div id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── DATA (langsung dari database, lihat blok PHP di atas <head>) ──────────
const novels = <?php echo json_encode($novelsForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

let cart = [];
let activeGenre = 'semua';
let searchQ = '';
let sortMode = 'default';
let selectedPay = '';
let currentStep = 1;

// ── RENDER GRID ──────────────────────────────────────────────────────────
function renderGrid() {
  let data = [...novels];

  // filter genre
  if (activeGenre !== 'semua') data = data.filter(n => n.genre === activeGenre);

  // search
  if (searchQ) {
    const q = searchQ.toLowerCase();
    data = data.filter(n =>
      n.title.toLowerCase().includes(q) ||
      n.author.toLowerCase().includes(q) ||
      n.genre.toLowerCase().includes(q)
    );
  }

  // sort
  if (sortMode === 'price-asc') data.sort((a,b) => a.price - b.price);
  else if (sortMode === 'price-desc') data.sort((a,b) => b.price - a.price);
  else if (sortMode === 'name-asc') data.sort((a,b) => a.title.localeCompare(b.title));
  else if (sortMode === 'rating-desc') data.sort((a,b) => b.rating - a.rating);

  document.getElementById('resultCount').textContent = data.length + ' novel';

  const grid = document.getElementById('grid');
  const noRes = document.getElementById('noResults');

  if (data.length === 0) { grid.innerHTML=''; noRes.style.display='block'; return; }
  noRes.style.display = 'none';

  grid.innerHTML = data.map((n,i) => `
    <div class="col-6 col-md-4 col-lg-3 rev" style="transition-delay:${i*40}ms">
      <div class="ncard" onclick="openDetail(${n.id})">
        <div class="ncard-cover">
          <div class="cover-art" style="background:${n.bg}">
            <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(255,255,255,.05),transparent)"></div>
            <div class="ca-ico">${n.icon}</div>
            <div class="ca-ttl" style="color:${n.color}">${n.title}</div>
            <div class="ca-aut" style="color:${n.color}">${n.author}</div>
          </div>
          ${n.badge ? `<span class="nc-badge ${n.badge==='new'?'b-new':n.badge==='hot'?'b-hot':'b-disc'}">${n.badge==='new'?'Baru':n.badge==='hot'?'Hot':'Diskon'}</span>` : ''}
        </div>
        <div class="nc-body">
          <div class="nc-genre">${n.genre}</div>
          <div class="nc-title">${n.title}</div>
          <div class="nc-author">oleh ${n.author}</div>
          <div class="nc-rating">★★★★${n.rating>=4.8?'★':'½'} <span>(${n.rating})</span></div>
          <div>
            <span class="nc-price">Rp ${n.price.toLocaleString('id')}</span>
            ${n.orig ? `<span class="nc-orig">Rp ${n.orig.toLocaleString('id')}</span>` : ''}
          </div>
          <button class="nc-btn" onclick="event.stopPropagation();addCart(${n.id})">
            <i class="bi bi-bag-plus me-1"></i>Beli Sekarang
          </button>
        </div>
      </div>
    </div>
  `).join('');

  // re-observe
  document.querySelectorAll('.rev:not(.on)').forEach(el => obs.observe(el));
}

// ── FILTERS ──────────────────────────────────────────────────────────────
function filterG(g, btn) {
  activeGenre = g;
  document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  renderGrid();
}

function handleSearch(inp) {
  searchQ = inp.value.trim();
  document.getElementById('clearBtn').classList.toggle('show', searchQ.length > 0);
  renderGrid();
}

function clearSearch() {
  searchQ = '';
  document.getElementById('searchInput').value = '';
  document.getElementById('clearBtn').classList.remove('show');
  renderGrid();
}

function sortBooks() {
  sortMode = document.getElementById('sortSelect').value;
  renderGrid();
}

// ── DETAIL MODAL ─────────────────────────────────────────────────────────
function openDetail(id) {
  const n = novels.find(x => x.id === id);
  if (!n) return;
  document.getElementById('detailCover').innerHTML = `
    <div class="cover-art" style="background:${n.bg};height:100%">
      <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(255,255,255,.05),transparent)"></div>
      <div style="font-size:3.5rem;position:relative;z-index:1;margin-bottom:.8rem">${n.icon}</div>
      <div style="font-family:var(--serif);font-size:1.4rem;font-weight:700;color:${n.color};position:relative;z-index:1;text-align:center;padding:0 2rem;line-height:1.2">${n.title}</div>
      <div style="font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:${n.color};opacity:.7;margin-top:.5rem;position:relative;z-index:1">${n.author}</div>
    </div>`;
  document.getElementById('detailGenre').textContent = n.genre.toUpperCase();
  document.getElementById('detailTitle').textContent = n.title;
  document.getElementById('detailAuthor').textContent = 'oleh ' + n.author;
  document.getElementById('detailRating').innerHTML = '★★★★' + (n.rating>=4.8?'★':'½') + ` <span style="color:var(--muted);font-size:.8rem">(${n.rating} / 5.0) · 200+ ulasan</span>`;
  document.getElementById('detailDesc').textContent = n.desc;
  document.getElementById('detailMeta').innerHTML = n.tags.map(t => `<span class="detail-tag">${t}</span>`).join('');
  document.getElementById('detailPrice').textContent = 'Rp ' + n.price.toLocaleString('id');
  document.getElementById('detailOrig').textContent = n.orig ? 'Rp ' + n.orig.toLocaleString('id') : '';
  document.getElementById('detailBuyBtn').onclick = () => { addCart(n.id); closeDetail(); };
  document.getElementById('detailOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDetail() {
  document.getElementById('detailOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

// ── CART ─────────────────────────────────────────────────────────────────
function addCart(id) {
  const n = novels.find(x => x.id === id);
  if (!n) return;
  const exists = cart.find(x => x.id === id);
  if (exists) { toast('Novel sudah ada di keranjang!'); return; }
  cart.push({...n});
  updateCart();
  toast(`✦ "${n.title}" ditambahkan ke keranjang!`);
}

function removeCart(id) {
  cart = cart.filter(x => x.id !== id);
  updateCart();
}

function clearCart() {
  cart = [];
  updateCart();
}

function updateCart() {
  const count = cart.length;
  document.querySelectorAll('.cart-count').forEach(el => {
    el.textContent = count;
    el.classList.toggle('show', count > 0);
  });

  const empty = document.getElementById('cartEmpty');
  const items = document.getElementById('cartItems');
  const footer = document.getElementById('cartFooter');

  if (count === 0) {
    empty.style.display = 'block';
    items.innerHTML = '';
    footer.style.display = 'none';
    return;
  }

  empty.style.display = 'none';
  footer.style.display = 'block';

  items.innerHTML = cart.map(n => `
    <div class="cart-item">
      <div class="ci-cover">
        <div class="cover-art" style="background:${n.bg};height:100%">
          <div style="font-size:1.5rem;position:relative;z-index:1">${n.icon}</div>
        </div>
      </div>
      <div class="ci-info">
        <div class="ci-title">${n.title}</div>
        <div class="ci-author">oleh ${n.author}</div>
        <div class="ci-price">Rp ${n.price.toLocaleString('id')}</div>
      </div>
      <button class="ci-remove" onclick="removeCart(${n.id})"><i class="bi bi-trash3"></i></button>
    </div>
  `).join('');

  const sub = cart.reduce((s,n) => s + n.price, 0);
  const disc = cart.reduce((s,n) => s + (n.orig ? n.orig - n.price : 0), 0);
  const total = sub;
  document.getElementById('cartSubtotal').textContent = 'Rp ' + sub.toLocaleString('id');
  document.getElementById('cartDisc').textContent = '- Rp ' + disc.toLocaleString('id');
  document.getElementById('cartTotal').textContent = 'Rp ' + total.toLocaleString('id');
}

function toggleCart() {
  document.getElementById('cartSidebar').classList.toggle('open');
  document.getElementById('cartOverlay').classList.toggle('open');
}

// ── CHECKOUT ─────────────────────────────────────────────────────────────
function openCheckout() {
  toggleCart();
  currentStep = 1;
  selectedPay = '';
  document.getElementById('successScreen').classList.remove('show');
  document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
  document.getElementById('step1').classList.add('active');
  updateStepUI(1);
  document.getElementById('checkoutOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeCheckout() {
  document.getElementById('checkoutOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function goStep(n) {
  if (n === 2) {
    const name = document.getElementById('co-name').value.trim();
    const email = document.getElementById('co-email').value.trim();
    if (!name || !email) { toast('⚠ Nama dan email wajib diisi!'); return; }
    if (!email.includes('@')) { toast('⚠ Format email tidak valid!'); return; }
  }
  if (n === 3) {
    if (!selectedPay) { toast('⚠ Pilih metode pembayaran terlebih dahulu!'); return; }
    fillSummary();
  }
  currentStep = n;
  document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');
  updateStepUI(n);
}

function updateStepUI(n) {
  [1,2,3].forEach(i => {
    document.getElementById('si'+i).className = 'si-num' + (i < n ? ' done' : i === n ? ' active' : '');
    document.getElementById('si'+i).textContent = i < n ? '✓' : i;
    document.getElementById('sl'+i).className = 'si-label' + (i === n ? ' active' : '');
  });
}

function selectPay(el, method) {
  document.querySelectorAll('.pay-opt').forEach(p => p.classList.remove('selected'));
  el.classList.add('selected');
  selectedPay = method;
}

function fillSummary() {
  const name = document.getElementById('co-name').value;
  const email = document.getElementById('co-email').value;
  const phone = document.getElementById('co-phone').value || '-';
  document.getElementById('buyerSummary').innerHTML = `${name} &nbsp;·&nbsp; ${email} &nbsp;·&nbsp; ${phone}`;
  const total = cart.reduce((s,n) => s + n.price, 0);
  document.getElementById('orderSummary').innerHTML = cart.map(n => `
    <div class="order-summary-item">
      <span>${n.title}</span>
      <span>Rp ${n.price.toLocaleString('id')}</span>
    </div>`).join('');
  document.getElementById('orderTotal').textContent = 'Rp ' + total.toLocaleString('id');
}

function doCheckout() {
  const name  = document.getElementById('co-name').value;
  const email = document.getElementById('co-email').value;
  const phone = document.getElementById('co-phone').value || '';

  // Simpan pesanan ke database (agar muncul di dashboard admin & pembeli)
  fetch('api/checkout.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name, email, phone,
      payment: selectedPay,
      items: cart.map(n => ({ id: n.id, title: n.title, price: n.price }))
    })
  }).catch(() => { /* toko tetap jalan meski API belum aktif */ });

  document.getElementById('successEmail').textContent = email;
  document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
  document.getElementById('successScreen').classList.add('show');
  [1,2,3].forEach(i => { document.getElementById('si'+i).className='si-num done'; document.getElementById('si'+i).textContent='✓'; });
  cart = [];
  updateCart();
}

// ── TOAST ─────────────────────────────────────────────────────────────────
function toast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ── FAQ TOGGLE ────────────────────────────────────────────────────────────
function toggleFaq(el) {
  const item = el.parentElement;
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(f => f.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}

// ── SCROLL REVEAL ────────────────────────────────────────────────────────
const obs = new IntersectionObserver(entries => {
  entries.forEach((e,i) => { if(e.isIntersecting) setTimeout(()=>e.target.classList.add('on'), i*60); });
}, { threshold: 0.08 });
document.querySelectorAll('.rev').forEach(el => obs.observe(el));

// ── NAVBAR SCROLL ────────────────────────────────────────────────────────
window.addEventListener('scroll', () => {
  document.getElementById('navbar').classList.toggle('scrolled', scrollY > 60);
});

// ── CLOSE ON ESC ────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeDetail(); closeCheckout(); }
});

// ── INIT ──────────────────────────────────────────────────────────────────
renderGrid();
</script>
</body>
</html>
