import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
import { Alert, Autosave, ColorPreview, Dropdown, Modal, Tabs, Popover, Toggle, Slideover } from "tailwindcss-stimulus-components"
app.register('alert', Alert)
app.register('autosave', Autosave)
app.register('color-preview', ColorPreview)
app.register('dropdown', Dropdown)
app.register('modal', Modal)
app.register('popover', Popover)
app.register('slideover', Slideover)
app.register('tabs', Tabs)
app.register('toggle', Toggle)
