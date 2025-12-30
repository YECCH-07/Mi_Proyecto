import { Link } from 'react-router-dom';
import { useState, useEffect } from 'react';

export default function Home() {
    const [currentImage, setCurrentImage] = useState(0);

    const images = [
        { src: '/images/haquira.jpg', name: 'Haquira' },
        { src: '/images/challhuahuacho.jpg', name: 'Challhuahuacho' },
        { src: '/images/cotabambas.jpg', name: 'Cotabambas' },
        { src: '/images/coyllurqui.jpg', name: 'Coyllurqui' }
    ];

    useEffect(() => {
        const interval = setInterval(() => {
            setCurrentImage((prev) => (prev + 1) % images.length);
        }, 5000); // Cambia cada 5 segundos

        return () => clearInterval(interval);
    }, []);

    return (
        <div className="relative min-h-screen">
            {/* Background Image Carousel */}
            <div className="fixed inset-0 z-0">
                {images.map((image, index) => (
                    <div
                        key={index}
                        className={`absolute inset-0 transition-opacity duration-1000 ${
                            index === currentImage ? 'opacity-100' : 'opacity-0'
                        }`}
                    >
                        <img
                            src={image.src}
                            alt={image.name}
                            className="w-full h-full object-cover"
                        />
                        {/* Overlay oscuro para legibilidad */}
                        <div className="absolute inset-0 bg-gradient-to-b from-black/60 via-black/50 to-black/70"></div>
                    </div>
                ))}
            </div>

            {/* Indicadores de carrusel */}
            <div className="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-10 flex space-x-2">
                {images.map((_, index) => (
                    <button
                        key={index}
                        onClick={() => setCurrentImage(index)}
                        className={`w-3 h-3 rounded-full transition-all ${
                            index === currentImage
                                ? 'bg-white w-8'
                                : 'bg-white/50 hover:bg-white/75'
                        }`}
                        aria-label={`Ver imagen ${index + 1}`}
                    />
                ))}
            </div>

            {/* Nombre del lugar actual */}
            <div className="fixed top-20 right-8 z-10 bg-black/30 backdrop-blur-sm px-4 py-2 rounded-lg">
                <p className="text-white text-sm font-semibold">
                    📍 {images[currentImage].name}
                </p>
            </div>

            {/* Contenido principal */}
            <div className="relative z-10">
                {/* Hero Section */}
                <div className="container mx-auto px-4 py-16">
                    <div className="text-center mb-12 mt-8">
                        <h1 className="text-5xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg">
                            Sistema de Denuncias Ciudadanas
                        </h1>
                        <p className="text-xl md:text-2xl text-white/90 max-w-3xl mx-auto drop-shadow-md">
                            Tu voz importa. Reporta problemas en tu comunidad y ayúdanos a mejorar nuestra región juntos.
                        </p>
                    </div>

                    {/* CTA Buttons */}
                    <div className="flex flex-col sm:flex-row justify-center gap-4 mb-16">
                        <Link
                            to="/consulta"
                            className="bg-white/95 hover:bg-white text-primary font-bold py-4 px-8 rounded-lg shadow-xl backdrop-blur-sm transition transform hover:scale-105 text-center"
                        >
                            🔍 Consultar mi Denuncia
                        </Link>
                        <Link
                            to="/register"
                            className="bg-primary/95 hover:bg-primary text-white font-bold py-4 px-8 rounded-lg shadow-xl backdrop-blur-sm transition transform hover:scale-105 text-center"
                        >
                            📝 Registrar Nueva Denuncia
                        </Link>
                    </div>

                    {/* Features Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                        <div className="bg-white/95 backdrop-blur-sm p-8 rounded-lg shadow-xl text-center transform hover:scale-105 transition">
                            <div className="text-5xl mb-4">📍</div>
                            <h3 className="text-xl font-bold text-gray-800 mb-2">Geolocalización</h3>
                            <p className="text-gray-600">
                                Marca la ubicación exacta del problema en el mapa para una atención precisa.
                            </p>
                        </div>

                        <div className="bg-white/95 backdrop-blur-sm p-8 rounded-lg shadow-xl text-center transform hover:scale-105 transition">
                            <div className="text-5xl mb-4">⚡</div>
                            <h3 className="text-xl font-bold text-gray-800 mb-2">Seguimiento en Tiempo Real</h3>
                            <p className="text-gray-600">
                                Consulta el estado de tu denuncia en cualquier momento con tu código único.
                            </p>
                        </div>

                        <div className="bg-white/95 backdrop-blur-sm p-8 rounded-lg shadow-xl text-center transform hover:scale-105 transition">
                            <div className="text-5xl mb-4">🔒</div>
                            <h3 className="text-xl font-bold text-gray-800 mb-2">Seguridad y Privacidad</h3>
                            <p className="text-gray-600">
                                Tu información está protegida. Solo tú puedes ver tus denuncias.
                            </p>
                        </div>
                    </div>

                    {/* How it Works */}
                    <div className="bg-white/95 backdrop-blur-sm rounded-lg shadow-xl p-8 mb-16">
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
                        <h2 className="text-3xl font-bold text-white mb-6 drop-shadow-lg">
                            Tipos de Denuncias que Puedes Reportar
                        </h2>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div className="bg-white/95 backdrop-blur-sm p-4 rounded-lg shadow-lg hover:shadow-xl transition">
                                <div className="text-3xl mb-2">🚧</div>
                                <p className="font-semibold">Baches en la Vía</p>
                            </div>
                            <div className="bg-white/95 backdrop-blur-sm p-4 rounded-lg shadow-lg hover:shadow-xl transition">
                                <div className="text-3xl mb-2">💡</div>
                                <p className="font-semibold">Falta de Alumbrado</p>
                            </div>
                            <div className="bg-white/95 backdrop-blur-sm p-4 rounded-lg shadow-lg hover:shadow-xl transition">
                                <div className="text-3xl mb-2">🗑️</div>
                                <p className="font-semibold">Acumulación de Basura</p>
                            </div>
                            <div className="bg-white/95 backdrop-blur-sm p-4 rounded-lg shadow-lg hover:shadow-xl transition">
                                <div className="text-3xl mb-2">⚠️</div>
                                <p className="font-semibold">Señalización Defectuosa</p>
                            </div>
                        </div>
                    </div>

                    {/* Final CTA */}
                    <div className="bg-white/95 backdrop-blur-sm text-gray-800 rounded-lg shadow-xl p-8 text-center mb-8">
                        <h2 className="text-3xl font-bold mb-4">¿Ya tienes un código de denuncia?</h2>
                        <p className="text-lg mb-6">Consulta el estado de tu reporte ingresando tu código único</p>
                        <Link
                            to="/consulta"
                            className="bg-primary text-white hover:bg-primary-dark font-bold py-3 px-8 rounded-lg shadow-lg transition inline-block transform hover:scale-105"
                        >
                            Consultar Ahora
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
