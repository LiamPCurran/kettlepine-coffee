/* Kettlepine static demo cart.
   The live store runs WooCommerce; this static portfolio snapshot swaps the server cart for a
   browser-only cart (localStorage). No orders are sent and no payment is taken. */
(function () {
  var me = document.currentScript || document.querySelector('script[src*="kp-cart.js"]');
  var BASE = me.src.replace(/\/kp-cart\.js.*$/, '');
  var PATH = new URL(BASE).pathname;
  var KEY = 'kp-demo-cart';
  var products = {};
  var money = function (n) { return '$' + n.toFixed(2); };
  var read = function () { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } };
  var write = function (c) { try { localStorage.setItem(KEY, JSON.stringify(c)); } catch (e) {} };
  var count = function (c) { return Object.keys(c).reduce(function (s, k) { return s + c[k]; }, 0); };

  var css = '.kp-cartpage *,.kp-cartpage *::before,.kp-cartpage *::after{box-sizing:border-box}.kp-toast{position:fixed;left:50%;bottom:24px;transform:translate(-50%,20px);opacity:0;background:#24170e;color:#fbf7f1;padding:14px 18px;border-radius:14px;box-shadow:0 20px 40px -20px rgba(0,0,0,.6);z-index:9999;display:flex;gap:16px;align-items:center;transition:.25s;font-size:.95rem;max-width:calc(100% - 32px)}'
    + '.kp-toast.show{opacity:1;transform:translate(-50%,0)}.kp-toast a{color:#e3a33b;font-weight:700;white-space:nowrap}'
    + '.kp-cartpage{padding-block:48px 80px}.kp-cartpage h1{font-size:clamp(2rem,4vw,3rem);margin:0 0 28px}'
    + '.kp-cart-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:40px;align-items:start}@media(max-width:860px){.kp-cart-grid{grid-template-columns:1fr}}'
    + '.kp-line{display:grid;grid-template-columns:84px 1fr auto;gap:16px;align-items:center;padding:16px 0;border-bottom:1px solid #e6dccd}'
    + '.kp-line img{width:84px;height:84px;object-fit:cover;border-radius:12px;background:#f2eadf}.kp-line a{color:inherit;text-decoration:none;font-weight:600}'
    + '.kp-qty{display:inline-flex;align-items:center;border:1px solid #d9cdbd;border-radius:999px;margin-top:6px}.kp-qty button{border:0;background:none;width:32px;height:32px;cursor:pointer;font-size:1.1rem}.kp-qty span{min-width:22px;text-align:center}'
    + '.kp-rm{background:none;border:0;color:#6b5a4a;text-decoration:underline;cursor:pointer;font-size:.85rem;margin-left:10px}'
    + '.kp-sum{background:#f2eadf;border-radius:18px;padding:24px}.kp-sum div{display:flex;justify-content:space-between;padding:6px 0}.kp-sum .tot{border-top:1px solid #d9cdbd;margin-top:8px;padding-top:12px;font-weight:700;font-size:1.15rem}'
    + '.kp-sum .kp-btn,.kp-pay{display:block;width:100%;text-align:center;margin-top:16px;border:0;cursor:pointer;font:inherit;font-weight:600}.kp-note{font-size:.88rem;color:#6b5a4a;margin-top:12px}'
    + '.kp-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}.kp-form .full{grid-column:1/-1}.kp-form label{display:block;font-weight:600;font-size:.9rem;margin-bottom:4px}'
    + '.kp-form input,.kp-form select{width:100%;padding:12px 14px;border:1.5px solid #d9cdbd;border-radius:10px;font:inherit;background:#fff}'
    + '.kp-pay{padding:.95rem 1.6rem;border-radius:999px;background:#2c5a46;color:#fff}.kp-done{background:#e3ece7;border-radius:18px;padding:28px}';
  var st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);

  function badge() {
    var n = count(read());
    document.querySelectorAll('a[href$="/cart/"]').forEach(function (a) {
      if (!a.dataset.kpLabel) a.dataset.kpLabel = a.textContent.trim();
      if (a.closest('.kp-cartpage,.kp-toast')) return;
      var span = a.querySelector('.wp-block-navigation-item__label') || a;
      span.textContent = a.dataset.kpLabel + (n ? ' (' + n + ')' : '');
    });
  }
  var tt;
  function toast(name) {
    var t = document.querySelector('.kp-toast') || document.body.appendChild(Object.assign(document.createElement('div'), { className: 'kp-toast', role: 'status' }));
    t.innerHTML = '<span>Added <strong></strong> to your cart</span><a href="' + BASE + '/cart/">View cart →</a>';
    t.querySelector('strong').textContent = name;
    t.classList.add('show'); clearTimeout(tt); tt = setTimeout(function () { t.classList.remove('show'); }, 3500);
  }
  function add(id, qty) {
    if (!products[id]) return;
    var c = read(); c[id] = (c[id] || 0) + qty; write(c); badge(); toast(products[id].name);
  }
  function idFrom(el) {
    if (el.dataset && el.dataset.product_id) return el.dataset.product_id;
    var li = el.closest('li[class*="post-"], .product[class*="post-"]');
    var m = li && li.className.match(/post-(\d+)/);
    return m ? m[1] : null;
  }

  document.addEventListener('click', function (e) {
    var b = e.target.closest('.add_to_cart_button, .wc-block-components-product-button button, .wc-block-components-product-button a, .wp-block-button.wc-block-components-product-button a');
    if (!b) return;
    var id = idFrom(b);
    if (!id || !products[id]) return;
    e.preventDefault(); e.stopImmediatePropagation(); add(id, 1);
  }, true);
  document.addEventListener('submit', function (e) {
    var f = e.target.closest('form.cart');
    if (!f) return;
    e.preventDefault(); e.stopImmediatePropagation();
    var btn = f.querySelector('[name="add-to-cart"]');
    var q = f.querySelector('input.qty');
    add(btn && btn.value, Math.max(1, parseInt(q && q.value, 10) || 1));
  }, true);

  function totals(c) {
    var sub = Object.keys(c).reduce(function (s, k) { return s + (products[k] ? products[k].price * c[k] : 0); }, 0);
    var ship = sub >= 50 || sub === 0 ? 0 : 8;
    return { sub: sub, ship: ship, tot: sub + ship };
  }
  function summary(c, cta) {
    var t = totals(c);
    return '<aside class="kp-sum"><div><span>Subtotal</span><span>' + money(t.sub) + '</span></div>'
      + '<div><span>Shipping</span><span>' + (t.ship ? money(t.ship) : 'Free') + '</span></div>'
      + (t.sub && t.sub < 50 ? '<p class="kp-note">Add ' + money(50 - t.sub) + ' more for free shipping.</p>' : '')
      + '<div class="tot"><span>Total (CAD)</span><span>' + money(t.tot) + '</span></div>' + cta + '</aside>';
  }
  function lines(c, editable) {
    return Object.keys(c).filter(function (k) { return products[k]; }).map(function (k) {
      var p = products[k];
      return '<div class="kp-line"><img src="' + p.img + '" alt=""><div><a href="' + p.url + '">' + p.name + '</a><br>'
        + (editable ? '<span class="kp-qty"><button data-q="-1" data-id="' + k + '" aria-label="Decrease quantity">−</button><span>' + c[k] + '</span><button data-q="1" data-id="' + k + '" aria-label="Increase quantity">+</button></span><button class="kp-rm" data-rm="' + k + '">Remove</button>' : '<small>Qty ' + c[k] + '</small>')
        + '</div><strong>' + money(p.price * c[k]) + '</strong></div>';
    }).join('');
  }
  var empty = '<p>Your cart is empty.</p><p><a class="kp-btn" href="' + BASE + '/shop/">Shop coffee</a></p>';
  function renderCart(el) {
    var c = read();
    if (!count(c)) { el.innerHTML = empty; return; }
    el.innerHTML = '<div class="kp-cart-grid"><div>' + lines(c, true) + '<p><a class="kp-link" href="' + BASE + '/shop/">← Continue shopping</a></p></div>'
      + summary(c, '<a class="kp-btn" href="' + BASE + '/checkout/">Proceed to checkout</a>') + '</div>';
  }
  function renderCheckout(el) {
    var c = read();
    if (!count(c)) { el.innerHTML = empty; return; }
    el.innerHTML = '<div class="kp-cart-grid"><form class="kp-form" id="kp-co">'
      + '<div class="full"><label for="co-email">Email</label><input id="co-email" type="email" autocomplete="email" required></div>'
      + '<div><label for="co-first">First name</label><input id="co-first" autocomplete="given-name" required></div>'
      + '<div><label for="co-last">Last name</label><input id="co-last" autocomplete="family-name" required></div>'
      + '<div class="full"><label for="co-addr">Address</label><input id="co-addr" autocomplete="street-address" required></div>'
      + '<div><label for="co-city">City</label><input id="co-city" autocomplete="address-level2" required></div>'
      + '<div><label for="co-prov">Province</label><select id="co-prov"><option>Ontario</option><option>Quebec</option><option>British Columbia</option><option>Alberta</option><option>Manitoba</option><option>Saskatchewan</option><option>Nova Scotia</option><option>New Brunswick</option><option>Newfoundland and Labrador</option><option>Prince Edward Island</option></select></div>'
      + '<div class="full"><label for="co-postal">Postal code</label><input id="co-postal" autocomplete="postal-code" required></div>'
      + '<div class="full"><button class="kp-pay" type="submit">Place demo order</button><p class="kp-note">Portfolio demo: no order is sent and no payment is taken. Don\'t enter real details.</p></div>'
      + '</form><div>' + lines(c, false) + summary(c, '') + '</div></div>';
    el.querySelector('#kp-co').addEventListener('submit', function (e) {
      e.preventDefault();
      write({}); badge();
      el.innerHTML = '<div class="kp-done"><h2>Thanks! (demo order)</h2><p>On the live WooCommerce store, this is where the customer gets an order confirmation email and the roastery gets a new order. In this static portfolio demo, nothing was sent or charged.</p><p><a class="kp-btn" href="' + BASE + '/shop/">Back to the shop</a></p></div>';
    });
  }
  document.addEventListener('click', function (e) {
    var q = e.target.closest('[data-q]'), rm = e.target.closest('[data-rm]');
    if (!q && !rm) return;
    var c = read();
    if (q) { c[q.dataset.id] = Math.max(0, (c[q.dataset.id] || 0) + parseInt(q.dataset.q, 10)); if (!c[q.dataset.id]) delete c[q.dataset.id]; }
    if (rm) delete c[rm.dataset.rm];
    write(c); badge(); renderCart(document.getElementById('kp-cart'));
  });

  fetch(BASE + '/assets/kp-products.json').then(function (r) { return r.json(); }).then(function (p) {
    products = p; badge();
    var cart = document.getElementById('kp-cart'), co = document.getElementById('kp-checkout');
    if (cart) renderCart(cart);
    if (co) renderCheckout(co);
  });
})();
