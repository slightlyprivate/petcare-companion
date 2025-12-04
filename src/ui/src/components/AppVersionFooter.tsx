export function AppVersionFooter() {
  const appVersion = import.meta.env.VITE_APP_VERSION ?? 'dev-local';

  // Parse version string (format: "0.9.0+0f60968" or "0f60968" or "dev-local")
  const parseVersion = (v: string) => {
    if (v.includes('+')) {
      const [version, build] = v.split('+');
      return { version, build };
    }
    // Legacy format or dev: just show as build
    if (v === 'dev-local' || v === 'dev') {
      return { version: null, build: 'local' };
    }
    // Assume it's just a SHA
    return { version: null, build: v };
  };

  const { version, build } = parseVersion(appVersion);

  return (
    <footer className="text-xs text-gray-500 py-4 text-center">
      PetCare Companion
      {version && (
        <>
          {' · '}
          <span className="font-mono">v{version}</span>
        </>
      )}
      {' · '}
      <span className="font-mono text-gray-400">Build: {build}</span>
    </footer>
  );
}
