document.addEventListener('DOMContentLoaded', function() {
    // Show the login tab by default
    showTab('login');
});

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });

    // Deactivate all nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    // Show the selected tab
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Activate the selected nav link
    const activeLink = document.querySelector(`.nav-link[href*="${tabName}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

document.getElementById('registroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    registrarUsuario();
});

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    loginUsuario();
});

async function registrarUsuario() {
    const dni = document.getElementById('dni').value;
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const address = document.getElementById('address').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('http://localhost:3000/api/users/citizens', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                dni, 
                first_name: firstName, 
                last_name: lastName, 
                email, 
                phone, 
                address, 
                password 
            })
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            document.getElementById('registroForm').reset();
            showTab('login'); // Switch to login tab after successful registration
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Registration failed:', error);
        alert('No se pudo conectar con el servidor. Inténtelo más tarde.');
    }
}

async function loginUsuario() {
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    try {
        const response = await fetch('http://localhost:3000/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (response.ok) {
            // TODO: Store the token (e.g., in localStorage) and update UI
            alert(result.message);
            console.log('Token:', result.token);
            // For now, just switch to the new report tab
            showTab('denuncia');
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        console.error('Login failed:', error);
        alert('No se pudo conectar con el servidor. Inténtelo más tarde.');
    }
}