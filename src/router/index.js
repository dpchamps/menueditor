import Vue from 'vue';
import Router from 'vue-router';
import Root from '@/components/Root';
import Login from '@/components/Login';
import Dashboard from '@/components/Dashboard';
import User from '@/components/User';
import Backup from '@/components/Backup';

Vue.use(Router);

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/',
      name: '',
      component: Root
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      children: [
        {
          name: 'page-view',
          path: '/dashboard/:page',
          component: Dashboard
        },
        {
          name:'subpage-view',
          path: '/dashboard/:page/:subpage',
          component: Dashboard
        },
        {
          name: 'section-view',
          path: '/dashboard/:page/:subpage/:section',
          component: Dashboard
        },
        {
          name: 'item-view',
          path: '/dashboard/:page/:subpage/:section/:item',
          component: Dashboard
        }
      ],
      component: Dashboard
    },
    {
      path: '/user',
      name: 'User',
      component: User
    },
    {
      path: '/backup',
      name: 'Backup',
      component: Backup
    }

  ]
})
