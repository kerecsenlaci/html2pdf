(function () {
  // csak a user űrlapon fusson
  if (!/\/front\/user\.form\.php/i.test(location.pathname)) return;

  // hol nyílik majd a modal? — ezt a PHP oldal már kirajzolja (modal helpered)
  // ha több van az oldalon, az elsőt használjuk
  function getModalOpenButtonId() {
    // a modal helpered mindig létrehoz egy nyitó ID-t, ami "html2pdf_open_user_"-ral kezdődik
    var open = document.querySelector('[id^="html2pdf_open_user_"]');
    return open ? open.id : null;
  }

  // beszúrjuk a gombot a kukába gomb elé, ha még nincs bent
  function insertButton() {
    var delBtn = document.querySelector('#main-form button[name="delete"]');
    if (!delBtn || !delBtn.parentNode) return false;

    // ha már betettük, ne tegyük újra
    if (document.getElementById('html2pdf_injected_user_btn')) return true;

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'html2pdf_injected_user_btn';
    btn.className = 'btn btn-primary me-2';
    btn.innerHTML = '<i class="ti ti-printer" style="margin-right:6px;"></i> Munkalap nyomtatása (html2pdf)';

    // kattintásra a modal „nyitó” elemére kattintunk (amit a PHP-s helper létrehozott)
    btn.addEventListener('click', function () {
      var openId = getModalOpenButtonId();
      if (openId) {
        var opener = document.getElementById(openId);
        if (opener) opener.click();
      }
    });

    delBtn.parentNode.insertBefore(btn, delBtn);
    return true;
  }

  // 1) azonnal próbálkozunk
  if (insertButton()) return;

  // 2) figyeljük a DOM-ot (tab AJAX betöltésnél is működik)
  var tries = 0;
  var iv = setInterval(function () {
    if (insertButton() || ++tries > 60) clearInterval(iv);
  }, 250);

  var obs = new MutationObserver(function () {
    if (insertButton()) {
      try { obs.disconnect(); } catch (e) {}
    }
  });
  try { obs.observe(document.body, { childList: true, subtree: true }); } catch (e) {}
  setTimeout(function () { try { obs.disconnect(); } catch (e) {} }, 20000);
})();
