import { useState, useEffect } from 'react';
import { jwtDecode } from 'jwt-decode';

export const useAuth = () => {
    const [authState, setAuthState] = useState({
        isAuthenticated: false,
        userRole: null,
        userId: null,
        userName: null,
        isLoading: true
    });

    useEffect(() => {
        checkAuth();
    }, []);

    const checkAuth = () => {
        const token = localStorage.getItem('jwt');

        if (!token) {
            setAuthState({
                isAuthenticated: false,
                userRole: null,
                userId: null,
                userName: null,
                isLoading: false
            });
            return;
        }

        try {
            const decoded = jwtDecode(token);

            // Check if token is expired
            if (decoded.exp * 1000 < Date.now()) {
                localStorage.removeItem('jwt');
                setAuthState({
                    isAuthenticated: false,
                    userRole: null,
                    userId: null,
                    userName: null,
                    isLoading: false
                });
                return;
            }

            setAuthState({
                isAuthenticated: true,
                userRole: decoded.data.rol,
                userId: decoded.data.id,
                userName: `${decoded.data.nombres} ${decoded.data.apellidos}`,
                isLoading: false
            });

        } catch (error) {
            console.error("Invalid token:", error);
            localStorage.removeItem('jwt');
            setAuthState({
                isAuthenticated: false,
                userRole: null,
                userId: null,
                userName: null,
                isLoading: false
            });
        }
    };

    const logout = () => {
        localStorage.removeItem('jwt');
        setAuthState({
            isAuthenticated: false,
            userRole: null,
            userId: null,
            userName: null,
            isLoading: false
        });
    };

    return { ...authState, logout, checkAuth };
};
