export function AppVersionFooter() {
  const version = import.meta.env.VITE_APP_VERSION ?? 'dev-local';

  return (
    <footer className="version-footer">
      PetCare Companion · <span>Build: {version}</span>
    </footer>
  );
}
