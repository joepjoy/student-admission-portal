// Client-side validation for the registration form
(function () {
  const form = document.getElementById('regForm');

  // ---- helpers -------------------------------------------------
  const $ = (id) => document.getElementById(id);

  function setError(id, msg) {
    $(id + 'Err').textContent = msg;
    $(id).classList.toggle('invalid', !!msg);
    $(id).classList.toggle('valid', !msg);
    return !msg;                       // true when field is OK
  }

  // ---- individual field validators ----------------------------
  const validators = {
    name() {
      const v = $('name').value.trim();
      if (!v) return setError('name', 'Name is required');
      if (!/^[A-Za-z .]{3,}$/.test(v)) return setError('name', 'Letters only, at least 3 characters');
      return setError('name', '');
    },
    roll_no() {
      const v = $('roll_no').value.trim();
      if (!v) return setError('roll_no', 'Roll number is required');
      // two digits (year) + 2-4 letters (dept) + 3 digits, e.g. 21CS001
      if (!/^\d{2}[A-Za-z]{2,4}\d{3}$/.test(v)) return setError('roll_no', 'Format: 21CS001');
      return setError('roll_no', '');
    },
    email() {
      const v = $('email').value.trim();
      if (!v) return setError('email', 'Email is required');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return setError('email', 'Enter a valid email address');
      return setError('email', '');
    },
    course() {
      return setError('course', $('course').value ? '' : 'Please select a course');
    },
    password() {
      const v = $('password').value;
      updateMeter(v);
      if (!v) return setError('password', 'Password is required');
      if (strength(v).score < 5)
        return setError('password', 'Need 8+ chars with upper, lower, digit and symbol');
      return setError('password', '');
    },
    confirm() {
      if (!$('confirm').value) return setError('confirm', 'Please confirm the password');
      if ($('confirm').value !== $('password').value) return setError('confirm', 'Passwords do not match');
      return setError('confirm', '');
    },
    photo() {
      const f = $('photo').files[0];
      if (!f) return setError('photo', 'Please choose a file');
      if (!/\.(jpe?g|png)$/i.test(f.name)) return setError('photo', 'Only JPG or PNG allowed');
      if (f.size > 2 * 1024 * 1024) return setError('photo', 'File must be 2 MB or smaller');
      return setError('photo', '');
    }
  };

  // ---- password strength ---------------------------------------
  function strength(p) {
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[a-z]/.test(p)) score++;
    if (/\d/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    return { score };
  }

  function updateMeter(p) {
    const s = strength(p).score;
    const levels = [
      ['0%', '#e3e8f4', ''],
      ['20%', '#d93025', 'Very weak'],
      ['40%', '#f06a1a', 'Weak'],
      ['60%', '#f4b400', 'Fair'],
      ['80%', '#7cb342', 'Good'],
      ['100%', '#1e8e3e', 'Strong']
    ];
    $('meterBar').style.width = levels[s][0];
    $('meterBar').style.background = levels[s][1];
    $('strengthText').textContent = levels[s][2];
  }

  // ---- wire up events -------------------------------------------
  Object.keys(validators).forEach((key) => {
    const el = $(key);
    el.addEventListener('blur', validators[key]);                       // validate when leaving a field
    el.addEventListener(key === 'photo' || key === 'course' ? 'change' : 'input',
      () => { if (el.classList.contains('invalid') || key === 'password') validators[key](); });
  });
  $('password').addEventListener('input', () => { if ($('confirm').value) validators.confirm(); });

  form.addEventListener('submit', function (e) {
    // run ALL validators (don't short-circuit so every error shows)
    const results = Object.keys(validators).map((k) => validators[k]());
    const allOk = results.every(Boolean);
    if (!allOk) {
      e.preventDefault();
      const firstBad = form.querySelector('.invalid');
      if (firstBad) firstBad.focus();
      $('okMsg').hidden = true;
    } else {
      $('okMsg').hidden = false;       // form posts to ../php/add.php
    }
  });

  $('resetBtn').addEventListener('click', () => {
    setTimeout(() => {
      form.querySelectorAll('.invalid,.valid').forEach((el) => el.classList.remove('invalid', 'valid'));
      form.querySelectorAll('.error').forEach((el) => (el.textContent = ''));
      updateMeter('');
      $('okMsg').hidden = true;
    }, 0);
  });
})();
