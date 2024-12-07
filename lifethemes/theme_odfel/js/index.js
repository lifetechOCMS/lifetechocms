$(document).ready(function () {
    // HERO CAROUSEL
    $('.hero-carousel').slick({
      dots: true,
      infinite: true,
      arrows: false,
      speed: 300,
      autoplay: true,
      autoplaySpeed: 3000
    });
  // WHAT STUDENT SAYS CAROUSEL
  $('.students-carousel').slick({
    dots: true,
    infinite: true,
    arrows: false,
    speed: 300,
    slidesToShow: 3,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 3000,
    responsive: [
      {
        breakpoint: 767.98,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  });
  // FAQ QUESTIONS
  let faqQuestions = [...document.querySelectorAll('.faq-header')]
  const toggleFaqQuestions = (currentFaqQuestion) => {
    if(currentFaqQuestion.parentNode.className.includes('faq-open')) {
      currentFaqQuestion.parentNode.classList.remove('faq-open')
    } else {
      faqQuestions.forEach(faqQuestion => {
        faqQuestion.parentNode.classList.remove('faq-open')
      })
      currentFaqQuestion.parentNode.classList.add('faq-open')
    }
  }
  faqQuestions.forEach(faqQuestion => faqQuestion.addEventListener('click', () => toggleFaqQuestions(faqQuestion)))
  //GALLERY FILTER
  let galleryTabButtons = [...document.querySelectorAll('.gallery-tab-btn')]
  let galleryImages = [...document.querySelectorAll('.gallery-item')]
  const toggleGalleryTabs = (currentGalleryTabButton) => {
    galleryTabButtons.forEach(galleryTabButton => galleryTabButton.classList.remove('active-button'))
    currentGalleryTabButton.classList.add('active-button')
    galleryImages.forEach(galleryImg => {
      galleryImg.classList.remove('img-shrink')
      galleryImg.classList.add('img-expand')
      const imgType = parseInt(galleryImg.dataset.img)
      const buttonType = parseInt(currentGalleryTabButton.dataset.btn)
      if(buttonType === 0) {
        galleryImg.classList.remove('img-shrink')
        galleryImg.classList.add('img-expand')
      } else if(imgType !== buttonType) {
        galleryImg.classList.add('img-shrink')
        galleryImg.classList.remove('img-expand')
      }
    })

  }
  galleryTabButtons.forEach(galleryTabButton => galleryTabButton.addEventListener('click', () => toggleGalleryTabs(galleryTabButton)))
  // LOGIN REGISTER
  let loginRegisterButtons = [...document.querySelectorAll('.login-register-btn')]
  let loginForm = document.querySelector('.login-form-holder')
  let registerForm = document.querySelector('.register-form-holder')
  const toggleLoginRegister = (currentLoginRegisterButton) => {
    loginRegisterButtons.forEach(loginRegisterButton => loginRegisterButton.classList.remove('active-button'))
    currentLoginRegisterButton.classList.add('active-button')
    if(currentLoginRegisterButton.className.includes('login-btn')) {
      loginForm.classList.add('active-form')
      registerForm.classList.remove('active-form')
    } else {
      if(currentLoginRegisterButton.className.includes('register-btn')) {
        registerForm.classList.add('active-form')
        loginForm.classList.remove('active-form')
      }
    }
  }
  loginRegisterButtons.forEach(loginRegisterButton => loginRegisterButton.addEventListener('click', () => toggleLoginRegister(loginRegisterButton)))
});
