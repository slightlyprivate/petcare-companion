import { Link } from 'react-router-dom';
import { PATHS } from '../../routes/paths';
import QueryBoundary from '../../components/QueryBoundary';
import { usePets } from '../../api/pets/hooks';
import type { Pet } from '../../api/types';

/**
 * Dashboard pets management page.
 * @returns Dashboard pets management component
 */
export default function DashboardPets() {
  const { data, isLoading, error } = usePets();
  const pets = data?.data ?? [];
  return (
    <div>
      <div className="flex items-center justify-between mb-3">
        <h1 className="text-xl font-semibold">My Pets</h1>
        <Link to={PATHS.DASHBOARD.PETS_NEW} className="text-sm text-brand-accent">
          + Add Pet
        </Link>
      </div>
      <QueryBoundary loading={isLoading} error={error}>
        {pets.length === 0 ? (
          <div className="text-sm text-brand-fg">You have no pets yet.</div>
        ) : (
          <ul className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {pets.map((p: Pet) => (
              <li key={p.id} className="border rounded p-3">
                <div className="font-medium">{p.name}</div>
                <div className="text-sm text-gray-600">{p.species}</div>
                <div className="mt-2 flex items-center gap-3 text-sm">
                  <Link className="text-brand-accent" to={PATHS.DASHBOARD.PET_DETAIL(p.id)}>
                    View
                  </Link>
                  <Link className="text-brand-accent" to={PATHS.DASHBOARD.PET_SETTINGS(p.id)}>
                    Settings
                  </Link>
                </div>
              </li>
            ))}
          </ul>
        )}
      </QueryBoundary>
    </div>
  );
}
