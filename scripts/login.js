const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

const formRegister = document.getElementById('formRegister');
const formLogin = document.getElementById('formLogin');

signUpButton.addEventListener('click', () => {
    container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
    container.classList.remove("right-panel-active");
});

formRegister.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(formRegister);
    const data = Object.fromEntries(formData.entries());

    if (data.telefonos) data.telefonos = [data.telefonos];
    const res = await fetch('/backend/api/auth.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });


    try {
        const result = await res.json();

        if (result.token) {
            localStorage.setItem('jwt', result.token);
            window.location.href = 'index.html';
        } else {
            alert(result.error);
        }
    } catch {
        alert("Credenciales inválidas")
    }
});

formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(formLogin);
    const data = Object.fromEntries(formData.entries());

    const res = await fetch('/backend/api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const result = await res.json();
    console.log(result)

    if (result.token) {
        localStorage.setItem('jwt', result.token);
        window.location.href = 'perfil.html';
    } else {
        alert(result.error);
    }
});