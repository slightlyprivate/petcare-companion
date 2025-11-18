import { Link } from 'react-router-dom';
import { PATHS } from '../../routes/paths';

/**
 * Landing page for the PetCare Companion application.
 * @returns Landing page component
 */
export default function Landing() {
  return (
    <section className="mx-auto max-w-3xl text-center py-12">
      <img
        src="/brand/illustrations/hero.png"
        alt="PetCare Companion Hero"
        className="w-full max-w-md mx-auto mb-6 rounded-lg shadow-lg"
      />
      <h1 className="text-3xl font-bold mb-4">PetCare Companion</h1>
      <p className="text-brand-fg mb-8">
        Discover pets, manage appointments, and share your pet's story.
      </p>
      <div className="flex items-center justify-center gap-3">
        <Link to={PATHS.DISCOVER} className="px-4 py-2 rounded bg-brand-accent text-white">
          Discover Pets
        </Link>
        <Link
          to={PATHS.AUTH.SIGNIN}
          className="px-4 py-2 rounded border border-brand-accent text-brand-accent"
        >
          Sign In / Get Started
        </Link>
      </div>
    </section>
  );
}
