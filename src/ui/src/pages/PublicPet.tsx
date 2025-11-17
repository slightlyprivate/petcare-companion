import { useParams, Link } from 'react-router-dom';
import { PATHS } from '../routes/paths';

/**
 * Public pet details page.
 * @returns Public pet details component
 */
export default function PublicPet() {
  const { slug } = useParams();
  return (
    <div>
      <div className="mb-3">
        <Link to={PATHS.DISCOVER} className="text-sm text-brand-accent">
          ← Back to Discover
        </Link>
      </div>
      <h1 className="text-xl font-semibold">Public Pet</h1>
      <p className="text-sm text-brand-fg">Slug: {slug}</p>
      <div className="mt-4 text-sm">SEO-friendly public pet details go here.</div>
    </div>
  );
}
