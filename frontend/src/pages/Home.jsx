import { Link } from 'react-router-dom';

export default function Home() {
    return (
        <div className="bg-gradient-to-b from-gray-50 to-white">
            {/* Hero Section */}
            <div className="container mx-auto px-4 py-16">
                <div className="text-center mb-12">
                    <h1 className="text-5xl font-bold text-primary mb-4">
                        Sistema de Denuncias Ciudadanas
                    </h1>
                    <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                        Tu voz importa. Reporta problemas en tu comunidad y ayúdanos a mejorar nuestra ciudad juntos.
                    </p>
                </div>

                {/* CTA Buttons */}
                <div className="flex flex-col sm:flex-row justify-center gap-4 mb-16">
                    <Link
                        to="/consulta"
                        className="bg-primary hover:bg-primary-dark text-white font-bold py-4 px-8 rounded-lg shadow-lg transition transform hover:scale-105 text-center"
                    >
                        🔍 Consultar mi Denuncia
                    </Link>
                    <Link
                        to="/register"
                        className="bg-white hover:bg-gray-100 text-primary font-bold py-4 px-8 rounded-lg shadow-lg border-2 border-primary transition transform hover:scale-105 text-center"
                    >
                        📝 Registrar Nueva Denuncia
                    </Link>
                </div>

                {/* Features Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <div className="bg-white p-8 rounded-lg shadow-md text-center">
                        <div className="text-5xl mb-4">📍</div>
                        <h3 className="text-xl font-bold text-gray-800 mb-2">Geolocalización</h3>
                        <p className="text-gray-600">
                            Marca la ubicación exacta del problema en el mapa para una atención precisa.
                        </p>
                    </div>

                    <div className="bg-white p-8 rounded-lg shadow-md text-center">
                        <div className="text-5xl mb-4">⚡</div>
                        <h3 className="text-xl font-bold text-gray-800 mb-2">Seguimiento en Tiempo Real</h3>
                        <p className="text-gray-600">
                            Consulta el estado de tu denuncia en cualquier momento con tu código único.
                        </p>
                    </div>

                    <div className="bg-white p-8 rounded-lg shadow-md text-center">
                        <div className="text-5xl mb-4">🔒</div>
                        <h3 className="text-xl font-bold text-gray-800 mb-2">Seguridad y Privacidad</h3>
                        <p className="text-gray-600">
                            Tu información está protegida. Solo tú puedes ver tus denuncias.
                        </p>
                    </div>
                </div>

                {/* How it Works */}
                <div className="bg-white rounded-lg shadow-lg p-8 mb-16">
                    <h2 className="text-3xl font-bold text-center text-gray-800 mb-8">
                        ¿Cómo Funciona?
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div className="text-center">
                            <div className="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                1
                            </div>
                            <h4 className="font-semibold text-gray-800 mb-2">Regístrate</h4>
                            <p className="text-gray-600 text-sm">Crea tu cuenta en menos de 2 minutos</p>
                        </div>
                        <div className="text-center">
                            <div className="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                2
                            </div>
                            <h4 className="font-semibold text-gray-800 mb-2">Reporta</h4>
                            <p className="text-gray-600 text-sm">Describe el problema y agrega fotos si tienes</p>
                        </div>
                        <div className="text-center">
                            <div className="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                3
                            </div>
                            <h4 className="font-semibold text-gray-800 mb-2">Recibe tu Código</h4>
                            <p className="text-gray-600 text-sm">Guarda el código para consultar el estado</p>
                        </div>
                        <div className="text-center">
                            <div className="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                                4
                            </div>
                            <h4 className="font-semibold text-gray-800 mb-2">Seguimiento</h4>
                            <p className="text-gray-600 text-sm">Revisa el progreso hasta su resolución</p>
                        </div>
                    </div>
                </div>

                {/* Categories */}
                <div className="text-center mb-16">
                    <h2 className="text-3xl font-bold text-gray-800 mb-6">
                        Tipos de Denuncias que Puedes Reportar
                    </h2>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div className="bg-gray-100 p-4 rounded-lg">
                            <div className="text-3xl mb-2">🚧</div>
                            <p className="font-semibold">Baches en la Vía</p>
                        </div>
                        <div className="bg-gray-100 p-4 rounded-lg">
                            <div className="text-3xl mb-2">💡</div>
                            <p className="font-semibold">Falta de Alumbrado</p>
                        </div>
                        <div className="bg-gray-100 p-4 rounded-lg">
                            <div className="text-3xl mb-2">🗑️</div>
                            <p className="font-semibold">Acumulación de Basura</p>
                        </div>
                        <div className="bg-gray-100 p-4 rounded-lg">
                            <div className="text-3xl mb-2">⚠️</div>
                            <p className="font-semibold">Señalización Defectuosa</p>
                        </div>
                    </div>
                </div>

                {/* Final CTA */}
                <div className="bg-primary text-white rounded-lg shadow-xl p-8 text-center">
                    <h2 className="text-3xl font-bold mb-4">¿Ya tienes un código de denuncia?</h2>
                    <p className="text-lg mb-6">Consulta el estado de tu reporte ingresando tu código único</p>
                    <Link
                        to="/consulta"
                        className="bg-white text-primary hover:bg-gray-100 font-bold py-3 px-8 rounded-lg shadow-lg transition inline-block"
                    >
                        Consultar Ahora
                    </Link>
                </div>
            </div>
        </div>
    );
}
