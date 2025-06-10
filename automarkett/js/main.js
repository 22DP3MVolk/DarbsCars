$(document).ready(() => {
  const savedLang = localStorage.getItem('language') || 'en';
  setLanguage(savedLang);

  // Обработка формы регистрации
  $('#register-form').submit(function(e) {
      e.preventDefault();
      $.ajax({
          url: 'register.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
              if (response.success) {
                  alert('Регистрация успешна! Теперь вы можете войти.');
                  window.location.href = 'login.html';
              } else {
                  alert('Ошибка: ' + response.error);
              }
          },
          error: function(xhr, status, error) {
              console.log(xhr.responseText);
              alert('Ошибка соединения с сервером: ' + error);
          }
      });
  });

  // Обработка формы логина
  $('#login-form').submit(function(e) {
      e.preventDefault();
      $.ajax({
          url: 'login.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
              if (response.success) {
                  alert('Вход успешен! Добро пожаловать, ' + response.user.username);
                  window.location.href = 'index.html';
              } else {
                  alert('Ошибка: ' + response.error);
              }
          },
          error: function(xhr, status, error) {
              console.log(xhr.responseText);
              alert('Ошибка соединения с сервером: ' + error);
          }
      });
  });

  // Обработка формы продажи машины
  $('#sell-form').submit(function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      $.ajax({
          url: 'add_car.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(response) {
              if (response.success) {
                  alert('Машина успешно выставлена на продажу!');
                  window.location.href = 'index.html';
              } else {
                  alert('Ошибка: ' + response.error);
              }
          },
          error: function(xhr, status, error) {
              console.log('Raw response:', xhr.responseText);
              alert('Ошибка соединения с сервером: ' + error);
          }
      });
  });
});