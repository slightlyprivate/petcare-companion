import { Suspense } from 'react';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import { routes } from './routes.config';
import Spinner from '../components/Spinner';

const router = createBrowserRouter(routes);

export default function AppRouter() {
  return (
    <Suspense fallback={<Spinner />}>
      <RouterProvider router={router} />
    </Suspense>
  );
}
