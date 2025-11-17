import { lazy } from 'react';
import { Outlet } from 'react-router-dom';
import RequireAuth from '../components/RequireAuth';
import RouteError from '../components/RouteError';
import NotFound from '../pages/NotFound';
import { PATHS } from './paths';
import PublicLayout from '../layouts/PublicLayout';
import AuthLayout from '../layouts/AuthLayout';
import DashboardLayout from '../layouts/DashboardLayout';

const Landing = lazy(() => import('../pages/Landing'));
const Discover = lazy(() => import('../pages/Discover'));
const PublicPet = lazy(() => import('../pages/PublicPet'));

const SignIn = lazy(() => import('../pages/SignIn'));
const SignUp = lazy(() => import('../pages/SignUp'));
const VerifyOtp = lazy(() => import('../pages/VerifyOtp'));

const DashboardPets = lazy(() => import('../pages/DashboardPets'));
const PetNew = lazy(() => import('../pages/PetNew'));
const PetDetail = lazy(() => import('../pages/PetDetail'));
const PetSettings = lazy(() => import('../pages/PetSettings'));
const DashboardAppointments = lazy(() => import('../pages/DashboardAppointments'));
const DashboardGifts = lazy(() => import('../pages/DashboardGifts'));
const DashboardAccount = lazy(() => import('../pages/DashboardAccount'));
const AdminGiftTypes = lazy(() => import('../pages/AdminGiftTypes'));

export const routes = [
  {
    element: <PublicLayout />,
    errorElement: <RouteError />,
    children: [
      { path: PATHS.ROOT, element: <Landing />, errorElement: <RouteError /> },
      { path: PATHS.DISCOVER, element: <Discover />, errorElement: <RouteError /> },
      { path: PATHS.PUBLIC.PET_DETAIL(), element: <PublicPet />, errorElement: <RouteError /> },
      {
        path: PATHS.AUTH.ROOT,
        element: <AuthLayout />,
        children: [
          { path: PATHS.AUTH.SIGNIN, element: <SignIn />, errorElement: <RouteError /> },
          { path: PATHS.AUTH.SIGNUP, element: <SignUp />, errorElement: <RouteError /> },
          { path: PATHS.AUTH.VERIFY, element: <VerifyOtp />, errorElement: <RouteError /> },
        ],
      },
      {
        path: PATHS.DASHBOARD.ROOT,
        element: (
          <RequireAuth>
            <DashboardLayout />
          </RequireAuth>
        ),
        children: [
          { index: true, element: <DashboardPets />, errorElement: <RouteError /> },
          { path: PATHS.DASHBOARD.PETS, element: <DashboardPets />, errorElement: <RouteError /> },
          { path: PATHS.DASHBOARD.PETS_NEW, element: <PetNew />, errorElement: <RouteError /> },
          {
            path: PATHS.DASHBOARD.PET_DETAIL(),
            element: <PetDetail />,
            errorElement: <RouteError />,
          },
          {
            path: PATHS.DASHBOARD.PET_SETTINGS(),
            element: <PetSettings />,
            errorElement: <RouteError />,
          },
          {
            path: PATHS.DASHBOARD.APPOINTMENTS,
            element: <DashboardAppointments />,
            errorElement: <RouteError />,
          },
          {
            path: PATHS.DASHBOARD.GIFTS,
            element: <DashboardGifts />,
            errorElement: <RouteError />,
          },
          {
            path: PATHS.DASHBOARD.ACCOUNT,
            element: <DashboardAccount />,
            errorElement: <RouteError />,
          },
          {
            path: PATHS.DASHBOARD.ADMIN.GIFT_TYPES,
            element: <AdminGiftTypes />,
            errorElement: <RouteError />,
          },
        ],
      },
      { path: '*', element: <NotFound />, errorElement: <RouteError /> },
    ],
  },
];
