#!/bin/bash

# =====================================================
# Script para cambiar entre entornos
# =====================================================

ENV=$1

if [ -z "$ENV" ]; then
    echo "❌ Error: Debes especificar el entorno"
    echo ""
    echo "Uso: ./switch-env.sh [local|production]"
    echo ""
    echo "Ejemplos:"
    echo "  ./switch-env.sh local       # Usar configuración local"
    echo "  ./switch-env.sh production  # Usar configuración de producción"
    exit 1
fi

case $ENV in
    local)
        echo "🔧 Cambiando a entorno LOCAL..."

        # Backend
        if [ -f ".env.local" ]; then
            cp .env.local backend/.env
            echo "✅ Backend configurado para LOCAL"
        else
            echo "⚠️  Advertencia: .env.local no encontrado"
        fi

        # Frontend
        if [ -f ".env.local" ]; then
            cp .env.local frontend/.env
            echo "✅ Frontend configurado para LOCAL"
        else
            echo "⚠️  Advertencia: .env.local no encontrado para frontend"
        fi

        echo ""
        echo "📝 Configuración actual:"
        echo "   - API URL: http://localhost:8000"
        echo "   - DB Host: database (Docker)"
        echo "   - Frontend: http://localhost:3000"
        echo ""
        echo "🐳 Para iniciar Docker: docker-compose up -d"
        ;;

    production)
        echo "🚀 Cambiando a entorno PRODUCCIÓN..."

        # Backend
        if [ -f ".env.production" ]; then
            cp .env.production backend/.env
            echo "✅ Backend configurado para PRODUCCIÓN"
        else
            echo "⚠️  Advertencia: .env.production no encontrado"
        fi

        # Frontend
        if [ -f ".env.production" ]; then
            cp .env.production frontend/.env
            echo "✅ Frontend configurado para PRODUCCIÓN"
        else
            echo "⚠️  Advertencia: .env.production no encontrado para frontend"
        fi

        echo ""
        echo "📝 Configuración actual:"
        echo "   - API URL: /birthdays/backend/public"
        echo "   - DB Host: localhost"
        echo "   - Frontend: https://kyz.com.ar/birthdays"
        echo ""
        echo "⚠️  IMPORTANTE:"
        echo "   1. Compilar frontend: cd frontend && npm run build"
        echo "   2. Subir archivos al servidor"
        echo "   3. Verificar credenciales sensibles en backend/.env"
        ;;

    *)
        echo "❌ Error: Entorno '$ENV' no válido"
        echo ""
        echo "Entornos disponibles:"
        echo "  - local"
        echo "  - production"
        exit 1
        ;;
esac

echo ""
echo "✨ Cambio de entorno completado!"
