</main>
<script>
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (ev) { if (!confirm(el.dataset.confirm)) ev.preventDefault(); });
});
</script>
</body>
</html>
