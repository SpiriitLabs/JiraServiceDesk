import './stimulus/bootstrap.ts';

const images = import.meta.glob('./images/**/*', {
    eager: true,
    import: 'default',
});

// import './styles/app.scss';
import './theme';

import 'simplebar';
import 'dragula';
