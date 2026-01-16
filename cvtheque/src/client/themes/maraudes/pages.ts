import CorePages from '@/core/pages';
import HomePages from './home';
import AuthenticationPages from './authentication';
import RecruitmentPages from './recruitment';
import AdminPages from './admin';

export default {
  ...AuthenticationPages,
  ...HomePages,
  ...CorePages,
  ...RecruitmentPages,
  ...AdminPages,
};
