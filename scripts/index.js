const userButton = document.getElementById("userButton");
const token = localStorage.getItem('jwt');
if (token) {
    fetch('/backend/api/perfil.php', {
        headers: { 'Authorization': `Bearer ${token}` }
    })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                userButton.innerText = "Perfil"
                userButton.href = "/perfil.html"
            } else {
                localStorage.removeItem('jwt');
                userButton.innerText = "Login"
                userButton.href = "/login.html"
            }
        })
        .catch(() => {
            localStorage.removeItem('jwt');
            userButton.innerText = "Login"
            userButton.href = "/login.html"
        });
} else {
    userButton.innerText = "Login"
    userButton.href = "/login.html"
}
