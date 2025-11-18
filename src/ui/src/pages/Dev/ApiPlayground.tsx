import AuthSection from './playground/AuthSection';
import PetsSection from './playground/PetsSection';
import GiftsSection from './playground/GiftsSection';
import CreditsSection from './playground/CreditsSection';

export default function ApiPlayground() {
  return (
    <div className="space-y-10">
      <h1 className="text-2xl font-semibold">API Playground</h1>
      <AuthSection />
      <PetsSection />
      <GiftsSection />
      <CreditsSection />
    </div>
  );
}
