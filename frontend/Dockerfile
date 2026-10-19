# ==============================================================================
# Dockerfile Multi-stage para Frontend Next.js (App Router / Standalone)
# Optimización de Capas y Reducción Radical de Superficie de Ataque
# ==============================================================================

# --- Etapa Base: Imagen base ligera con Node.js 20 sobre Alpine Linux ---
FROM node:20-alpine AS base
WORKDIR /app
# libc6-compat necesario para compatibilidad de binarios en Alpine
RUN apk add --no-cache libc6-compat

# --- Etapa 1: Instalación de Dependencias (deps) ---
# Se copian únicamente los manifiestos de paquetes para aprovechar la caché de capas de Docker
FROM base AS deps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline --no-audit

# --- Etapa 2: Compilación de la Aplicación (builder) ---
# Compila el código fuente produciendo el bundle optimizado "standalone"
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Desactivar telemetría de Next.js durante la construcción
ENV NEXT_TELEMETRY_DISABLED=1
ENV NODE_ENV=production

# Ejecutar el build de producción
RUN npm run build

# --- Etapa 3: Imagen Final de Producción (runner) ---
# Contiene únicamente los binarios y assets mínimos indispensables para ejecución
FROM base AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV PORT=3000
ENV HOSTNAME="0.0.0.0"

# Principio de menor privilegio: Crear usuario y grupo de sistema sin privilegios root
RUN addgroup --system --gid 1001 nodejs && \
    adduser --system --uid 1001 nextjs

# Copiar directorio público de assets
COPY --from=builder /app/public ./public

# Crear directorio .next y ajustar permisos para el usuario nextjs
RUN mkdir .next && chown nextjs:nodejs .next

# Copiar el runtime standalone compilado y los assets estáticos
# output: 'standalone' empaqueta de forma automática solo las dependencias estrictamente necesarias
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

# Asignar usuario sin privilegios
USER nextjs

# Exponer el puerto de la aplicación
EXPOSE 3000

# Healthcheck para validación de disponibilidad del contenedor
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost:3000/ || exit 1

# Comando de inicio del servidor Node.js autónomo
CMD ["node", "server.js"]
