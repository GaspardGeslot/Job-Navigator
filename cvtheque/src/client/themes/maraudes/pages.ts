import CorePages from '@/core/pages';
import HomePages from './home';
import AuthenticationPages from './authentication';
import RecruitmentPages from './recruitment';

export default {
  ...AuthenticationPages,
  ...HomePages,
  ...CorePages,
  ...RecruitmentPages,
};
