function switchForm(type) {
  const container = document.getElementById('form-container');
  container.classList.remove('show-login', 'show-register');
  container.classList.add(type === 'login' ? 'show-login' : 'show-register');
}
