export default function Loading() {
  return (
    <div class="min-h-[60vh] flex flex-col items-center justify-center gap-4">
      <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-slate-600 font-semibold animate-pulse">
        Cargando datos con Server Components...
      </p>
    </div>
  );
}
