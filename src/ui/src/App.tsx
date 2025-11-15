import { useQuery } from '@tanstack/react-query';

type PublicPet = {
  id: number;
  name: string;
  species: string;
};

export default function App() {
  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['public-pets'],
    queryFn: async () => {
      const res = await fetch('/api/public/pets');
      if (!res.ok) throw new Error(`Failed to fetch pets: ${res.status}`);
      return res.json() as Promise<{ data?: PublicPet[] } | PublicPet[]>;
    },
  });

  const pets: PublicPet[] = Array.isArray(data) ? data : (data?.data ?? []);

  return (
    <div className="min-h-screen p-6">
      <header className="mb-6">
        <h1 className="text-2xl font-semibold">PetCare Companion</h1>
        <p className="text-gray-600">Vite + React + Tailwind + TanStack Query</p>
      </header>

      <section className="bg-white border rounded-lg p-4 shadow-sm">
        <h2 className="text-lg font-medium mb-3">Public Pets</h2>

        {isLoading && <p className="text-gray-500">Loading pets…</p>}
        {isError && (
          <p className="text-red-600">Error: {(error as Error)?.message}</p>
        )}
        {!isLoading && !isError && (
          <ul className="space-y-2">
            {pets.length === 0 && (
              <li className="text-gray-500">No pets found.</li>
            )}
            {pets.map((pet) => (
              <li key={pet.id} className="flex items-center justify-between border rounded p-3">
                <div>
                  <div className="font-medium">{pet.name}</div>
                  <div className="text-sm text-gray-600">{pet.species}</div>
                </div>
                <span className="text-xs text-white bg-indigo-600 rounded px-2 py-1">Public</span>
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  );
}

