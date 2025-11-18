import QueryBoundary from '../../components/QueryBoundary';
import { usePublicPets } from '../../api/pets/hooks';
import { Link } from 'react-router-dom';
import { PATHS } from '../../routes/paths';
import type { Pet } from '../../api/types';

/**
 * Home page displaying a list of public pets.
 */
export default function Home() {
  const { data, isLoading, error } = usePublicPets();
  const pets = data?.data ?? [];
  return (
    <div>
      <h1 className="text-xl font-semibold mb-3">Public Pets</h1>
      <QueryBoundary loading={isLoading} error={error}>
        <ul className="space-y-2">
          {pets.map((p: Pet & { slug?: string }) => (
            <li key={p.id} className="border rounded p-3 flex justify-between">
              <div>
                <div className="font-medium">{p.name}</div>
                <div className="text-sm text-gray-600">{p.species}</div>
              </div>
              <Link
                className="text-indigo-600 text-sm"
                to={PATHS.PUBLIC.PET_DETAIL((p.slug ?? p.id) as string)}
              >
                View
              </Link>
            </li>
          ))}
        </ul>
      </QueryBoundary>
    </div>
  );
}
