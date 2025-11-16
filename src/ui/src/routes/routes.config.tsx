import { lazy } from 'react';
import RequireAuth from '../components/RequireAuth';
import AppLayout from '../layouts/AppLayout';

const Home = lazy(() => import('../pages/Home'));
const LoginOtp = lazy(() => import('../pages/LoginOtp'));
const Dashboard = lazy(() => import('../pages/Dashboard'));
const PetDetail = lazy(() => import('../pages/PetDetail'));
const Purchases = lazy(() => import('../pages/Purchases'));

export const routes = [
  {
    path: '/',
    element: <Home />,
  },
  {
    path: '/login',
    element: <LoginOtp />,
  },
  {
    element: (
      <RequireAuth>
        <AppLayout />
      </RequireAuth>
    ),
    children: [
      { path: '/dashboard', element: <Dashboard /> },
      { path: '/pets/:id', element: <PetDetail /> },
      { path: '/purchases', element: <Purchases /> },
    ],
  },
];

