export function AppVersionFooter() {
  const version = import.meta.env.VITE_APP_VERSION ?? 'dev-local';

  return (
    <footer className="text-xs text-gray-500 py-4 text-center">
      PetCare Companion · <span className="font-mono">Build: {version}</span>
    </footer>
  );
}
