$(document).ready(function () {
    const div    = document.getElementById('upcoming_meetings');
    const inc    = 4;

    function scroll()
    {
        if ((div.scrollHeight - div.clientHeight - div.scrollTop) < inc) { div.scrollTop = 0; }
        $("#upcoming_meetings").scrollTop(div.scrollTop + inc);
    }
    if (window.innerWidth == 1080 && window.innerHeight == 1920) {
        let needed = 1920
                   -  140 // Footer
                   - div.offsetTop;

        div.style.height = needed + 'px';
        setInterval(scroll, 500);
    }
});
