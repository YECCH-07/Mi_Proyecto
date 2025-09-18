<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CiudadSegura - Plataforma de Denuncias Ciudadanas</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .header p {
            color: #7f8c8d;
            text-align: center;
            font-size: 1.1em;
        }

        .nav-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 25px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .tab-btn:hover, .tab-btn.active {
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .tab-content {
            display: none;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
        }

        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .complaint-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .complaint-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }

        .status-pendiente { background: #e74c3c; }
        .status-en-proceso { background: #f39c12; }
        .status-resuelto { background: #27ae60; }

        #map {
            height: 400px;
            border-radius: 15px;
            margin: 20px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .stat-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .user-type-selector {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }

        .user-type-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            min-width: 150px;
        }

        .user-type-card:hover, .user-type-card.selected {
            border-color: #667eea;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-5px);
        }

        .photo-upload {
            border: 2px dashed #667eea;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin: 20px 0;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-upload:hover {
            background: #e9ecef;
            border-color: #764ba2;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 5px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏛️ CiudadSegura</h1>
            <p>Plataforma Ciudadana para la Denuncia de Problemas Urbanos</p>
            <p><small>ODS 11: Ciudades Sostenibles | ODS 16: Instituciones Sólidas</small></p>
        </div>

        <div class="nav-tabs">
            <button class="tab-btn active" onclick="showTab('registro')">👥 Registro</button>
            <button class="tab-btn" onclick="showTab('denuncia')">📱 Nueva Denuncia</button>
            <button class="tab-btn" onclick="showTab('seguimiento')">📊 Seguimiento</button>
            <button class="tab-btn" onclick="showTab('dashboard')">🎛️ Dashboard Autoridades</button>
            <button class="tab-btn" onclick="showTab('estadisticas')">📈 Estadísticas</button>
        </div>

        <!-- TAB REGISTRO -->
        <div id="registro" class="tab-content active">
            <h2>📝 Registro de Usuario</h2>
            <div class="user-type-selector">
                <div class="user-type-card selected" onclick="selectUserType('ciudadano')">
                    <h3>👤</h3>
                    <h4>Ciudadano</h4>
                    <p>Reportar problemas urbanos</p>
                </div>
                <div class="user-type-card" onclick="selectUserType('autoridad')">
                    <h3>🏛️</h3>
                    <h4>Autoridad</h4>
                    <p>Gestionar denuncias</p>
                </div>
            </div>

            <form id="registroForm">
                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" id="nombre" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="tel" id="telefono" required>
                </div>
                <div class="form-group" id="institucionGroup" style="display:none;">
                    <label>Institución:</label>
                    <select id="institucion">
                        <option>Municipalidad</option>
                        <option>Ministerio de Transporte</option>
                        <option>Servicio de Limpieza</option>
                        <option>Policía Nacional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" id="password" required>
                </div>
                <button type="submit" class="btn">✅ Registrarse</button>
            </form>
        </div>

        <!-- TAB DENUNCIA -->
        <div id="denuncia" class="tab-content">
            <h2>📱 Nueva Denuncia</h2>
            <form id="denunciaForm">
                <div class="form-group">
                    <label>Tipo de Problema:</label>
                    <select id="tipoproblema" required>
                        <option value="">Seleccionar...</option>
                        <option value="baches">🕳️ Baches en la vía</option>
                        <option value="alumbrado">💡 Falta de alumbrado público</option>
                        <option value="basura">🗑️ Acumulación de basura</option>
                        <option value="semaforos">🚦 Semáforos dañados</option>
                        <option value="alcantarillado">🚰 Problemas de alcantarillado</option>
                        <option value="otros">⚠️ Otros</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Descripción del Problema:</label>
                    <textarea id="descripcion" rows="4" placeholder="Describa detalladamente el problema..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Dirección/Ubicación:</label>
                    <input type="text" id="direccion" placeholder="Ingrese la dirección" required>
                </div>

                <div id="map"></div>

                <div class="photo-upload" onclick="document.getElementById('foto').click()">
                    <h3>📸 Subir Foto</h3>
                    <p>Haga clic para seleccionar una imagen del problema</p>
                    <input type="file" id="foto" accept="image/*" style="display:none;">
                </div>

                <div class="form-group">
                    <label>Nivel de Urgencia:</label>
                    <select id="urgencia" required>
                        <option value="baja">🟢 Baja</option>
                        <option value="media">🟡 Media</option>
                        <option value="alta">🔴 Alta</option>
                        <option value="critica">⚫ Crítica</option>
                    </select>
                </div>

                <button type="submit" class="btn">🚀 Enviar Denuncia</button>
            </form>
        </div>

        <!-- TAB SEGUIMIENTO -->
        <div id="seguimiento" class="tab-content">
            <h2>📊 Seguimiento de Denuncias</h2>
            <div class="form-group">
                <label>Buscar por código de denuncia:</label>
                <input type="text" id="codigoBusqueda" placeholder="Ingrese el código de su denuncia">
                <button class="btn" onclick="buscarDenuncia()">🔍 Buscar</button>
            </div>

            <div id="denunciasLista">
                <div class="complaint-card">
                    <h3>Bache en Av. Principal <span class="status-badge status-en-proceso">EN PROCESO</span></h3>
                    <p><strong>Código:</strong> DN-2024-001</p>
                    <p><strong>Fecha:</strong> 15 de Septiembre, 2024</p>
                    <p><strong>Descripción:</strong> Gran bache que causa daños a los vehículos</p>
                    <p><strong>Ubicación:</strong> Av. Principal con Jr. Los Olivos</p>
                    <p><strong>Última actualización:</strong> Equipo técnico asignado - 16 Sep 2024</p>
                </div>

                <div class="complaint-card">
                    <h3>Falta de alumbrado <span class="status-badge status-resuelto">RESUELTO</span></h3>
                    <p><strong>Código:</strong> DN-2024-002</p>
                    <p><strong>Fecha:</strong> 12 de Septiembre, 2024</p>
                    <p><strong>Descripción:</strong> Postes de luz sin funcionamiento</p>
                    <p><strong>Ubicación:</strong> Jr. Las Flores cuadra 5</p>
                    <p><strong>Resolución:</strong> Luminarias reemplazadas - 14 Sep 2024</p>
                </div>

                <div class="complaint-card">
                    <h3>Acumulación de basura <span class="status-badge status-pendiente">PENDIENTE</span></h3>
                    <p><strong>Código:</strong> DN-2024-003</p>
                    <p><strong>Fecha:</strong> 17 de Septiembre, 2024</p>
                    <p><strong>Descripción:</strong> Basura acumulada por varios días</p>
                    <p><strong>Ubicación:</strong> Mercado Central - Jr. Comercio</p>
                    <p><strong>Estado:</strong> En espera de asignación de equipo</p>
                </div>
            </div>
        </div>

        <!-- TAB DASHBOARD -->
        <div id="dashboard" class="tab-content">
            <h2>🎛️ Dashboard - Panel de Autoridades</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>47</h3>
                    <p>Denuncias Activas</p>
                </div>
                <div class="stat-card">
                    <h3>156</h3>
                    <p>Resueltas este mes</p>
                </div>
                <div class="stat-card">
                    <h3>85%</h3>
                    <p>Tasa de resolución</p>
                </div>
                <div class="stat-card">
                    <h3>2.3</h3>
                    <p>Días promedio</p>
                </div>
            </div>

            <h3>🚨 Denuncias Prioritarias</h3>
            <div class="complaint-card">
                <h3>Semáforo dañado - Intersección Principal <span class="status-badge status-pendiente">CRÍTICA</span></h3>
                <p><strong>Reportado:</strong> Hace 2 horas</p>
                <p><strong>Ubicación:</strong> Av. Libertadores con Av. Universitaria</p>
                <button class="btn" onclick="asignarEquipo('DN-2024-004')">👥 Asignar Equipo</button>
                <button class="btn" onclick="cambiarEstado('DN-2024-004')">🔄 Cambiar Estado</button>
            </div>

            <div class="complaint-card">
                <h3>Fuga de agua potable <span class="status-badge status-pendiente">ALTA</span></h3>
                <p><strong>Reportado:</strong> Hace 4 horas</p>
                <p><strong>Ubicación:</strong> Jr. San Martín cuadra 8</p>
                <button class="btn" onclick="asignarEquipo('DN-2024-005')">👥 Asignar Equipo</button>
                <button class="btn" onclick="cambiarEstado('DN-2024-005')">🔄 Cambiar Estado</button>
            </div>
        </div>

        <!-- TAB ESTADÍSTICAS -->
        <div id="estadisticas" class="tab-content">
            <h2>📈 Estadísticas y Reportes</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>324</h3>
                    <p>Total Denuncias</p>
                    <small>Este año</small>
                </div>
                <div class="stat-card">
                    <h3>276</h3>
                    <p>Resueltas</p>
                    <small>85.2% del total</small>
                </div>
                <div class="stat-card">
                    <h3>48</h3>
                    <p>En Proceso</p>
                    <small>14.8% del total</small>
                </div>
                <div class="stat-card">
                    <h3>3.2</h3>
                    <p>Días Promedio</p>
                    <small>Tiempo de resolución</small>
                </div>
            </div>

            <h3>📊 Problemas más reportados</h3>
            <div style="margin: 20px 0;">
                <div style="display: flex; align-items: center; margin: 10px 0;">
                    <span style="width: 200px;">🕳️ Baches</span>
                    <div style="background: #667eea; height: 20px; width: 80%; border-radius: 10px; margin: 0 10px;"></div>
                    <span>89 reportes</span>
                </div>
                <div style="display: flex; align-items: center; margin: 10px 0;">
                    <span style="width: 200px;">💡 Alumbrado</span>
                    <div style="background: #764ba2; height: 20px; width: 65%; border-radius: 10px; margin: 0 10px;"></div>
                    <span>67 reportes</span>
                </div>
                <div style="display: flex; align-items: center; margin: 10px 0;">
                    <span style="width: 200px;">🗑️ Basura</span>
                    <div style="background: #f39c12; height: 20px; width: 55%; border-radius: 10px; margin: 0 10px;"></div>
                    <span>54 reportes</span>
                </div>
                <div style="display: flex; align-items: center; margin: 10px 0;">
                    <span style="width: 200px;">🚦 Semáforos</span>
                    <div style="background: #e74c3c; height: 20px; width: 35%; border-radius: 10px; margin: 0 10px;"></div>
                    <span>32 reportes</span>
                </div>
            </div>

            <h3>🏆 Impacto en la Comunidad</h3>
            <div class="complaint-card">
                <h4>✅ Logros Destacados:</h4>
                <ul style="margin: 15px 0; padding-left: 20px;">
                    <li>276 problemas urbanos resueltos este año</li>
                    <li>Reducción del 40% en tiempo de respuesta</li>
                    <li>95% de satisfacción ciudadana</li>
                    <li>12 barrios beneficiados con mejoras</li>
                    <li>Participación activa de 1,247 ciudadanos registrados</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let map;
        let denuncias = [];
        let usuarioTipo = 'ciudadano';
        let denunciaCounter = 4;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            setupEventListeners();
        });

        function initMap() {
            // Coordenadas de Lima, Perú
            map = L.map('map').setView([-12.0464, -77.0428], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                // Limpiar marcadores previos
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Marker) {
                        map.removeLayer(layer);
                    }
                });

                // Agregar nuevo marcador
                L.marker([lat, lng]).addTo(map);
                
                // Geocodificación inversa simulada
                document.getElementById('direccion').value = `Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}`;
            });
        }

        function setupEventListeners() {
            // Formulario de registro
            document.getElementById('registroForm').addEventListener('submit', function(e) {
                e.preventDefault();
                registrarUsuario();
            });

            // Formulario de denuncia
            document.getElementById('denunciaForm').addEventListener('submit', function(e) {
                e.preventDefault();
                enviarDenuncia();
            });

            // Upload de foto
            document.getElementById('foto').addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    document.querySelector('.photo-upload h3').textContent = '✅ Foto seleccionada: ' + e.target.files[0].name;
                }
            });
        }

        function showTab(tabName) {
            // Ocultar todas las tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar tab seleccionada
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            // Inicializar mapa si se muestra la tab de denuncia
            if (tabName === 'denuncia') {
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            }
        }

        function selectUserType(tipo) {
            usuarioTipo = tipo;
            
            // Actualizar selección visual
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.target.closest('.user-type-card').classList.add('selected');

            // Mostrar/ocultar campos según el tipo
            const institucionGroup = document.getElementById('institucionGroup');
            if (tipo === 'autoridad') {
                institucionGroup.style.display = 'block';
            } else {
                institucionGroup.style.display = 'none';
            }
        }

        function registrarUsuario() {
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            
            // Simulación de registro
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <h4>✅ Registro Exitoso</h4>
                <p>Bienvenido ${nombre}! Tu cuenta como ${usuarioTipo} ha sido creada.</p>
                <p>Código de usuario: USR-${Math.floor(Math.random() * 10000).toString().padStart(4, '0')}</p>
            `;
            
            document.getElementById('registroForm').appendChild(successDiv);
            
            // Limpiar formulario
            document.getElementById('registroForm').reset();
        }

        function enviarDenuncia() {
            const tipo = document.getElementById('tipoproblema').value;
            const descripcion = document.getElementById('descripcion').value;
            const direccion = document.getElementById('direccion').value;
            const urgencia = document.getElementById('urgencia').value;

            const codigo = `DN-2024-${denunciaCounter.toString().padStart(3, '0')}`;
            denunciaCounter++;

            // Crear nueva denuncia
            const nuevaDenuncia = {
                codigo: codigo,
                tipo: tipo,
                descripcion: descripcion,
                direccion: direccion,
                urgencia: urgencia,
                fecha: new Date().toLocaleDateString('es-ES'),
                estado: 'pendiente'
            };

            denuncias.push(nuevaDenuncia);

            // Mostrar mensaje de éxito
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <h4>🚀 Denuncia Enviada Exitosamente</h4>
                <p>Tu denuncia ha sido registrada con el código: <strong>${codigo}</strong></p>
                <p>Recibirás notificaciones sobre el progreso por email.</p>
                <p>Tiempo estimado de respuesta: 2-5 días hábiles</p>
            `;
            
            document.getElementById('denunciaForm').appendChild(successDiv);

            // Limpiar formulario
            document.getElementById('denunciaForm').reset();
            
            // Limpiar mapa
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });

            // Actualizar lista de seguimiento
            actualizarListaDenuncias();
        }

        function buscarDenuncia() {
            const codigo = document.getElementById('codigoBusqueda').value;
            
            if (codigo) {
                // Simulación de búsqueda
                alert(`Buscando denuncia con código: ${codigo}\n\nEsta funcionalidad conectaría con la base de datos para mostrar el estado específico.`);
            }
        }

        function asignarEquipo(codigo) {
