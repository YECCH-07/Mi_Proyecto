import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import NuevaDenuncia from './pages/NuevaDenuncia';
import ConsultaPage from './pages/ConsultaPage';
import HeatmapPage from './pages/HeatmapPage';
import Unauthorized from './pages/Unauthorized';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import ProtectedRoute from './components/ProtectedRoute';

// Role-specific dashboards
import AdminDashboard from './pages/admin/AdminDashboard';
import SupervisorDashboard from './pages/supervisor/SupervisorDashboard';
import OperadorDashboard from './pages/operador/OperadorDashboard';
import DetalleDenunciaOperador from './pages/operador/DetalleDenunciaOperador';
import MisDenuncias from './pages/ciudadano/MisDenuncias';
import DetalleDenuncia from './pages/ciudadano/DetalleDenuncia';

function App() {
  return (
    <Router future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
      <div className="flex flex-col min-h-screen bg-gray-50">
        {/* Header/Navbar */}
        <Navbar />

        {/* Main Content */}
        <main className="flex-grow">
          <Routes>
            {/* Public routes */}
            <Route path="/" element={<Home />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route path="/unauthorized" element={<Unauthorized />} />

            {/* Admin routes */}
            <Route
              path="/admin/dashboard"
              element={
                <ProtectedRoute allowedRoles={['admin']}>
                  <AdminDashboard />
                </ProtectedRoute>
              }
            />

            {/* Supervisor routes */}
            <Route
              path="/supervisor/dashboard"
              element={
                <ProtectedRoute allowedRoles={['supervisor']}>
                  <SupervisorDashboard />
                </ProtectedRoute>
              }
            />

            {/* Operador routes */}
            <Route
              path="/operador/dashboard"
              element={
                <ProtectedRoute allowedRoles={['operador']}>
                  <OperadorDashboard />
                </ProtectedRoute>
              }
            />
            <Route
              path="/operador/denuncia/:id"
              element={
                <ProtectedRoute allowedRoles={['operador', 'supervisor', 'admin']}>
                  <DetalleDenunciaOperador />
                </ProtectedRoute>
              }
            />

            {/* Ciudadano routes */}
            <Route
              path="/ciudadano/mis-denuncias"
              element={
                <ProtectedRoute allowedRoles={['ciudadano']}>
                  <MisDenuncias />
                </ProtectedRoute>
              }
            />
            <Route
              path="/ciudadano/denuncia/:id"
              element={
                <ProtectedRoute allowedRoles={['ciudadano']}>
                  <DetalleDenuncia />
                </ProtectedRoute>
              }
            />

            {/* Public route - Consulta por código (sin login) */}
            <Route path="/consulta" element={<ConsultaPage />} />

            {/* Protected routes - requires authentication (any role) */}
            <Route
              path="/nueva-denuncia"
              element={
                <ProtectedRoute allowedRoles={['admin', 'supervisor', 'operador', 'ciudadano']}>
                  <NuevaDenuncia />
                </ProtectedRoute>
              }
            />

            <Route
              path="/heatmap"
              element={
                <ProtectedRoute allowedRoles={['admin', 'supervisor', 'operador']}>
                  <HeatmapPage />
                </ProtectedRoute>
              }
            />
          </Routes>
        </main>

        {/* Footer */}
        <Footer />
      </div>
    </Router>
  );
}

export default App;
