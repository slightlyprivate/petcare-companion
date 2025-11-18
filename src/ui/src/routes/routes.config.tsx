import { lazy } from 'react';
import { Outlet } from 'react-router-dom';
import RequireAuth from '../components/RequireAuth';
import RouteError from '../components/RouteError';
import NotFound from '../pages/Public/NotFound';
import { PATHS } from './paths';
import PublicLayout from '../layouts/PublicLayout';
import AuthLayout from '../layouts/AuthLayout';
import DashboardLayout from '../layouts/DashboardLayout';

const Landing = lazy(() => import('../pages/Public/Landing'));
const Discover = lazy(() => import('../pages/Public/Discover'));
const PublicPet = lazy(() => import('../pages/Public/PublicPet'));

const SignIn = lazy(() => import('../pages/Auth/SignIn'));
const SignUp = lazy(() => import('../pages/Auth/SignUp'));
const VerifyOtp = lazy(() => import('../pages/Auth/VerifyOtp'));

const DashboardPets = lazy(() => import('../pages/Dashboard/DashboardPets'));
const PetNew = lazy(() => import('../pages/Dashboard/PetNew'));
const PetDetail = lazy(() => import('../pages/Dashboard/PetDetail'));
const PetSettings = lazy(() => import('../pages/Dashboard/PetSettings'));
const DashboardAppointments = lazy(() => import('../pages/Dashboard/DashboardAppointments'));
const DashboardGifts = lazy(() => import('../pages/Dashboard/DashboardGifts'));
const DashboardAccount = lazy(() => import('../pages/Dashboard/DashboardAccount'));
const AdminGiftTypes = lazy(() => import('../pages/Dashboard/Admin/AdminGiftTypes'));

// Dev pages
const DevHome = lazy(() => import('../pages/Dev/Home'));
const DevDashboard = lazy(() => import('../pages/Dev/Dashboard'));
const DevPurchases = lazy(() => import('../pages/Dev/Purchases'));
const DevApiPlayground = lazy(() => import('../pages/Dev/ApiPlayground'));

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
      {
        path: '/dev',
        element: <Outlet />,
        children: [
          { index: true, element: <DevHome />, errorElement: <RouteError /> },
          { path: 'home', element: <DevHome />, errorElement: <RouteError /> },
          { path: 'dashboard', element: <DevDashboard />, errorElement: <RouteError /> },
          { path: 'purchases', element: <DevPurchases />, errorElement: <RouteError /> },
          { path: 'playground', element: <DevApiPlayground />, errorElement: <RouteError /> },
        ],
      },
      { path: '*', element: <NotFound />, errorElement: <RouteError /> },
    ],
  },
];
