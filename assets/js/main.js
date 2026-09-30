/* Silver Oak school website — vanilla JavaScript, no dependencies. */
(function () {
  "use strict";

  /* ------------------------- sticky header shadow ------------------------ */
  var header = document.getElementById("siteHeader");
  if (header) {
    var onScroll = function () {
      header.classList.toggle("is-stuck", window.scrollY > 20);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ----------------------------- mobile menu ---------------------------- */
  var burger = document.getElementById("burger");
  var nav = document.getElementById("nav");
  if (burger && nav) {
    burger.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      burger.setAttribute("aria-expanded", open ? "true" : "false");
    });
    nav.addEventListener("click", function (e) {
      if (e.target.closest("a") && window.innerWidth < 992) {
        nav.classList.remove("is-open");
        burger.setAttribute("aria-expanded", "false");
      }
    });
  }

  /* --------------------------- scroll reveal ---------------------------- */
  var revealEls = document.querySelectorAll(".reveal");
  if (revealEls.length && "IntersectionObserver" in window) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-in");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );
    revealEls.forEach(function (el) {
      io.observe(el);
    });
  } else {
    revealEls.forEach(function (el) {
      el.classList.add("is-in");
    });
  }

  /* ------------------------- animated counters -------------------------- */
  var counters = document.querySelectorAll("[data-count]");
  var runCounter = function (el) {
    var target = parseInt(el.getAttribute("data-count"), 10) || 0;
    var started = false;
    var animate = function () {
      if (started) return;
      started = true;
      var start = performance.now();
      var dur = 1400;
      var tick = function (now) {
        var p = Math.min((now - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased).toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };
    if ("IntersectionObserver" in window) {
      var obs = new IntersectionObserver(
        function (entries) {
          if (entries[0].isIntersecting) {
            animate();
            obs.disconnect();
          }
        },
        { threshold: 0.4 }
      );
      obs.observe(el);
    } else {
      animate();
    }
  };
  counters.forEach(runCounter);

  /* ------------------------------ lightbox ------------------------------ */
  var lb = document.getElementById("lightbox");
  if (lb) {
    var lbImg = document.getElementById("lightboxImg");
    var lbCap = document.getElementById("lightboxCap");
    var group = [];
    var index = 0;

    var show = function (i) {
      if (!group.length) return;
      index = (i + group.length) % group.length;
      var item = group[index];
      lbImg.setAttribute("src", item.getAttribute("data-full") || item.src);
      lbImg.setAttribute("alt", item.alt || "");
      lbCap.textContent = item.getAttribute("data-caption") || "";
    };

    var open = function (img, list) {
      group = list;
      var i = Array.prototype.indexOf.call(list, img);
      show(i < 0 ? 0 : i);
      lb.hidden = false;
      document.body.style.overflow = "hidden";
    };

    var close = function () {
      lb.hidden = true;
      document.body.style.overflow = "";
      lbImg.setAttribute("src", "");
    };

    document.querySelectorAll("[data-lightbox-group]").forEach(function (holder) {
      var imgs = Array.prototype.slice.call(holder.querySelectorAll("[data-lightbox]"));
      imgs.forEach(function (img) {
        img.addEventListener("click", function (e) {
          e.preventDefault();
          open(img, imgs);
        });
      });
    });

    lb.querySelector(".lb-close").addEventListener("click", close);
    lb.querySelector(".lb-prev").addEventListener("click", function () { show(index - 1); });
    lb.querySelector(".lb-next").addEventListener("click", function () { show(index + 1); });
    lb.addEventListener("click", function (e) {
      if (e.target === lb) close();
    });
    document.addEventListener("keydown", function (e) {
      if (lb.hidden) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") show(index - 1);
      if (e.key === "ArrowRight") show(index + 1);
    });
  }

  /* ---------------------- confirm before destructive -------------------- */
  document.querySelectorAll("form[data-confirm]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      var msg = form.getAttribute("data-confirm");
      if (!window.confirm(msg)) e.preventDefault();
    });
  });

  /* --------------------------- upload dropzone -------------------------- */
  document.querySelectorAll('input[type="file"][data-drop]').forEach(function (input) {
    var zone = input.closest(".dropzone") || input.parentElement;
    ["dragenter", "dragover"].forEach(function (ev) {
      zone.addEventListener(ev, function (e) {
        e.preventDefault();
        zone.classList.add("is-over");
      });
    });
    ["dragleave", "drop"].forEach(function (ev) {
      zone.addEventListener(ev, function (e) {
        e.preventDefault();
        zone.classList.remove("is-over");
      });
    });
    zone.addEventListener("drop", function (e) {
      if (e.dataTransfer && e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
      }
    });
  });

  /* ------------------------- live image preview ------------------------- */
  document.querySelectorAll("input[data-preview][type=\"file\"]").forEach(function (input) {
    var target = document.querySelector(input.getAttribute("data-preview"));
    if (!target) return;
    input.addEventListener("change", function () {
      var file = input.files && input.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (ev) {
        target.setAttribute("src", ev.target.result);
        target.hidden = false;
      };
      reader.readAsDataURL(file);
    });
  });

  /* ------------------------------ toasts -------------------------------- */
  var toast = function (message, kind) {
    var box = document.createElement("div");
    box.className = "alert " + (kind === "error" ? "alert-err" : "alert-ok");
    box.style.cssText =
      "position:fixed;z-index:400;bottom:22px;left:50%;transform:translateX(-50%);" +
      "box-shadow:0 18px 40px -18px rgba(22,35,59,.4);max-width:90vw;";
    box.textContent = message;
    document.body.appendChild(box);
    setTimeout(function () {
      box.style.transition = "opacity .4s";
      box.style.opacity = "0";
      setTimeout(function () { box.remove(); }, 420);
    }, 3800);
  };
  document.querySelectorAll("[data-toast]").forEach(function (el) {
    toast(el.getAttribute("data-toast"), el.getAttribute("data-toast-kind"));
  });
})();
