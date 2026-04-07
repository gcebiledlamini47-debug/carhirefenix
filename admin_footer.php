<?php // admin_footer.php (flat structure) ?>
        </div><!-- end admin-content -->
    </div><!-- end admin-main -->
</div><!-- end admin-layout -->

<script>
function toggleNotif() {
    var dd = document.getElementById('notifDropdown');
    if (dd.classList.contains('show')) {
        dd.classList.remove('show');
    } else {
        dd.classList.add('show');
    }
}
function markRead(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'mark_read.php?id=' + id, true);
    xhr.send();
    var badge = document.querySelector('.notif-badge');
    if (badge) { badge.style.display = 'none'; }
}
document.addEventListener('click', function(e) {
    var wrap = document.querySelector('.notif-wrap');
    if (wrap && !wrap.contains(e.target)) {
        var dd = document.getElementById('notifDropdown');
        if (dd) dd.classList.remove('show');
    }
});
</script>
<script src="main.js"></script>
</body>
</html>