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

import ClaimEvent from './pages/ClaimEvent.vue';
import SaveClaimEvent from './pages/SaveClaimEvent.vue';
import EditClaimEvent from './pages/EditClaimEvent.vue';
import ClaimExpenseType from './pages/claimExpenseTypes/ClaimExpenseType.vue';
import SaveClaimExpenseType from './pages/claimExpenseTypes/SaveClaimExpenseType.vue';
import EditClaimExpenseType from './pages/claimExpenseTypes/EditClaimExpenseType.vue';
import SubmitClaimRequest from './pages/submitClaim/SubmitClaimRequest.vue';
import SubmitClaim from './pages/submitClaim/SubmitClaim.vue';
import MyClaims from './pages/myClaims/MyClaims.vue';
import AssignClaimRequest from './pages/assignClaim/AssignClaimRequest.vue';
import AssignClaim from './pages/assignClaim/AssignClaim.vue';
import EmployeeClaims from './pages/employeeClaims/EmployeeClaims.vue';

export default {
  'claim-event': ClaimEvent,
  'claim-event-create': SaveClaimEvent,
  'claim-event-edit': EditClaimEvent,
  'claim-expense-types': ClaimExpenseType,
  'claim-expense-type-create': SaveClaimExpenseType,
  'claim-expense-type-edit': EditClaimExpenseType,
  'submit-claim-request': SubmitClaimRequest,
  'submit-claim': SubmitClaim,
  'my-claim': MyClaims,
  'assign-claim-request': AssignClaimRequest,
  'assign-claim': AssignClaim,
  'employee-claim': EmployeeClaims,
};
