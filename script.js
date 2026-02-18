document.addEventListener('DOMContentLoaded',function(){
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(a){
      a.addEventListener('click',function(e){
        var href=this.getAttribute('href');
        if(href && href.length>1 && document.querySelector(href)){
          e.preventDefault();
          document.querySelector(href).scrollIntoView({behavior:'smooth',block:'start'});
          // Close mobile nav if open
          var navLinks = document.querySelector('.nav-links');
          var hamburger = document.querySelector('.hamburger');
          if(navLinks && navLinks.classList.contains('open')){
            navLinks.classList.remove('open');
            if(hamburger) hamburger.setAttribute('aria-expanded','false');
          }
        }
      });
    });

    // Reveal animation
    try{
      var io = new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting){entry.target.classList.add('revealed');io.unobserve(entry.target);}});},{threshold:0.12});
      document.querySelectorAll('.reveal').forEach(function(el){io.observe(el)});
    }catch(e){}

    // Hamburger menu toggle
    var hamburger = document.querySelector('.hamburger');
    var navLinks = document.querySelector('.nav-links');
    if(hamburger && navLinks){
      hamburger.addEventListener('click',function(){
        var expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!expanded));
        navLinks.classList.toggle('open');
      });
    }

    // Contact form → mailto
    const contactForm = document.querySelector('form.form-card');
    if (contactForm) {
      contactForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const nameField = contactForm.querySelector('#name') || contactForm.querySelector('#contact-name');
        const emailField = contactForm.querySelector('#email') || contactForm.querySelector('#contact-email');
        const messageField = contactForm.querySelector('#message') || contactForm.querySelector('#contact-message');
        
        if(!nameField || !emailField || !messageField) return;
        
        const name = nameField.value;
        const email = emailField.value;
        const message = messageField.value;
        
        const subject = `Website contact from ${name}`;
        const body = `Name: ${name}\nEmail: ${email}\n\nMessage:\n${message}`;
        
        const mailtoLink = `mailto:devtechsoft5@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        
        window.location.href = mailtoLink;
      });
    }
  });