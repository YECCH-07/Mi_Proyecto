export default function Footer() {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-primary text-white mt-auto">
      {/* Sección Principal */}
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">

          {/* Columna 1: Información de la Municipalidad */}
          <div>
            <div className="flex items-center space-x-3 mb-4">
              <div className="bg-white rounded-lg p-2 shadow-md">
                <img
                  src="/logo-municipalidad.png"
                  alt="Logo"
                  className="h-12 w-12 object-contain"
                  onError={(e) => {
                    e.target.style.display = 'none';
                    e.target.nextSibling.style.display = 'flex';
                  }}
                />
                <div className="h-12 w-12 bg-white rounded flex items-center justify-center hidden">
                  <span className="text-primary text-2xl">🏛️</span>
                </div>
              </div>
              <div>
                <h3 className="text-white font-bold text-lg">
                  Municipalidad
                </h3>
                <p className="text-white/80 text-sm">
                  Sistema de Denuncias Ciudadanas
                </p>
              </div>
            </div>
            <p className="text-sm text-white/70 leading-relaxed">
              Trabajamos para mejorar la calidad de vida de nuestros ciudadanos.
              Tu denuncia es importante para nosotros.
            </p>
          </div>

          {/* Columna 2: Contacto de Gerencias */}
          <div>
            <h3 className="text-white font-bold text-lg mb-4 border-b border-white/20 pb-2">
              📞 Contacto de Gerencias
            </h3>
            <ul className="space-y-3 text-sm">
              <li>
                <div className="text-white font-semibold">Gerencia General</div>
                <a href="tel:+51987654321" className="text-primary-light hover:text-white transition">
                  📱 +51 987 654 321
                </a>
              </li>
              <li>
                <div className="text-white font-semibold">Gerencia de Obras Públicas</div>
                <a href="tel:+51987654322" className="text-primary-light hover:text-white transition">
                  📱 +51 987 654 322
                </a>
              </li>
              <li>
                <div className="text-white font-semibold">Gerencia de Servicios Municipales</div>
                <a href="tel:+51987654323" className="text-primary-light hover:text-white transition">
                  📱 +51 987 654 323
                </a>
              </li>
              <li>
                <div className="text-white font-semibold">Gerencia de Desarrollo Social</div>
                <a href="tel:+51987654324" className="text-primary-light hover:text-white transition">
                  📱 +51 987 654 324
                </a>
              </li>
            </ul>
          </div>

          {/* Columna 3: Correos Electrónicos y Páginas Web */}
          <div>
            <h3 className="text-white font-bold text-lg mb-4 border-b border-white/20 pb-2">
              📧 Información Digital
            </h3>

            {/* Correos Electrónicos */}
            <div className="mb-6">
              <h4 className="text-white font-semibold text-sm mb-2">Correos Electrónicos</h4>
              <ul className="space-y-2 text-sm">
                <li>
                  <div className="text-white/70">Mesa de Partes:</div>
                  <a
                    href="mailto:mesadepartes@municipalidad.gob.pe"
                    className="text-primary-light hover:text-white transition break-all"
                  >
                    mesadepartes@municipalidad.gob.pe
                  </a>
                </li>
                <li>
                  <div className="text-white/70">Denuncias:</div>
                  <a
                    href="mailto:denuncias@municipalidad.gob.pe"
                    className="text-primary-light hover:text-white transition break-all"
                  >
                    denuncias@municipalidad.gob.pe
                  </a>
                </li>
                <li>
                  <div className="text-white/70">Soporte Técnico:</div>
                  <a
                    href="mailto:soporte@municipalidad.gob.pe"
                    className="text-primary-light hover:text-white transition break-all"
                  >
                    soporte@municipalidad.gob.pe
                  </a>
                </li>
              </ul>
            </div>

            {/* Páginas Web */}
            <div>
              <h4 className="text-white font-semibold text-sm mb-2">Páginas Web</h4>
              <ul className="space-y-2 text-sm">
                <li>
                  <div className="text-white/70">Portal Principal:</div>
                  <a
                    href="https://www.municipalidad.gob.pe"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-primary-light hover:text-white transition flex items-center"
                  >
                    www.municipalidad.gob.pe
                    <svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </li>
                <li>
                  <div className="text-white/70">Transparencia:</div>
                  <a
                    href="https://transparencia.municipalidad.gob.pe"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-primary-light hover:text-white transition flex items-center"
                  >
                    transparencia.municipalidad.gob.pe
                    <svg className="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      {/* Sección Inferior - Redes Sociales y Copyright */}
      <div className="bg-primary-dark border-t border-white/10">
        <div className="container mx-auto px-4 py-6">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">

            {/* Redes Sociales */}
            <div className="flex items-center space-x-4">
              <span className="text-white/80 text-sm">Síguenos:</span>
              <a
                href="https://facebook.com/municipalidad"
                target="_blank"
                rel="noopener noreferrer"
                className="text-white/80 hover:text-white transition transform hover:scale-110"
                aria-label="Facebook"
              >
                <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a
                href="https://twitter.com/municipalidad"
                target="_blank"
                rel="noopener noreferrer"
                className="text-white/80 hover:text-white transition transform hover:scale-110"
                aria-label="Twitter"
              >
                <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
              </a>
              <a
                href="https://instagram.com/municipalidad"
                target="_blank"
                rel="noopener noreferrer"
                className="text-white/80 hover:text-white transition transform hover:scale-110"
                aria-label="Instagram"
              >
                <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                </svg>
              </a>
              <a
                href="https://youtube.com/municipalidad"
                target="_blank"
                rel="noopener noreferrer"
                className="text-white/80 hover:text-white transition transform hover:scale-110"
                aria-label="YouTube"
              >
                <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </a>
            </div>

            {/* Copyright */}
            <div className="text-sm text-white/80 text-center md:text-right">
              <p>
                © {currentYear} Municipalidad. Todos los derechos reservados.
              </p>
              <p className="text-xs mt-1 text-white/60">
                Desarrollado con ❤️ para nuestros ciudadanos
              </p>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
