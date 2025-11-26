/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

import CorePages from '@/core/pages';
import AdminPages from '../constructys/orangehrmAdminPlugin';
import PimPages from '../constructys/orangehrmPimPlugin';
import HelpPages from '../constructys/orangehrmHelpPlugin';
import TimePages from '../constructys/orangehrmTimePlugin';
import LeavePages from '../constructys/orangehrmLeavePlugin';
import OAuthPages from '../constructys/orangehrmCoreOAuthPlugin';
import AttendancePages from '../constructys/orangehrmAttendancePlugin';
import MaintenancePages from '../constructys/orangehrmMaintenancePlugin';
import RecruitmentPages from '../constructys/orangehrmRecruitmentPlugin';
import PerformancePages from '../constructys/orangehrmPerformancePlugin';
import CorporateDirectoryPages from '../constructys/orangehrmCorporateDirectoryPlugin';
import authenticationPages from '../constructys/orangehrmAuthenticationPlugin';
import dashboardPages from '../constructys/orangehrmDashboardPlugin';
import buzzPages from '../constructys/orangehrmBuzzPlugin';
import systemCheckPages from '../constructys/orangehrmSystemCheckPlugin';
import claimPages from '../constructys/orangehrmClaimPlugin';
import MaraudesPages from './maraudesPlugin';

export default {
  ...AdminPages,
  ...PimPages,
  ...CorePages,
  ...HelpPages,
  ...TimePages,
  ...OAuthPages,
  ...LeavePages,
  ...AttendancePages,
  ...MaintenancePages,
  ...RecruitmentPages,
  ...PerformancePages,
  ...CorporateDirectoryPages,
  ...authenticationPages,
  ...dashboardPages,
  ...buzzPages,
  ...systemCheckPages,
  ...claimPages,
  ...MaraudesPages,
};
