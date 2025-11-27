document.addEventListener('DOMContentLoaded', function(){
    // Mobile nav toggle
    var btn = document.getElementById('nav-toggle');
    var nav = document.getElementById('site-nav');
    if(btn && nav){
        btn.addEventListener('click', function(){
            var active = nav.classList.toggle('active');
            // update aria-expanded for a11y
            var expanded = active ? 'true' : 'false';
            btn.setAttribute('aria-expanded', expanded);
        });
    }

    // Simple reveal on scroll
    var reveals = document.querySelectorAll('.reveal');
    function handleReveal(){
        for(var i=0;i<reveals.length;i++){
            var r = reveals[i];
            var top = r.getBoundingClientRect().top;
            if(top < window.innerHeight - 60){ r.classList.add('visible'); }
        }
    }
    handleReveal();
    window.addEventListener('scroll', handleReveal, {passive:true});
});
