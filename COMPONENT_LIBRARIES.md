# Component Libraries Setup Guide

This project uses both **PrimeVue** and **Flowbite** component libraries. This guide explains how to use both libraries together.

## Installed Libraries

- **PrimeVue** v4.4.1 - A comprehensive Vue.js UI component library
- **Flowbite** v4.0.1 - A component library built on top of Tailwind CSS
- **PrimeIcons** v7.0.0 - Icon library for PrimeVue

## Configuration

### PrimeVue Configuration

PrimeVue is configured in `resources/js/config/primevue.ts` with the Aura theme preset. The configuration includes:

- **Theme**: Aura preset (supports light/dark mode)
- **Ripple Effect**: Enabled
- **Dark Mode**: Automatically detects `.dark` class on the document

### Flowbite Configuration

Flowbite components are imported directly - no plugin registration is needed. Flowbite works seamlessly with Tailwind CSS v4.

## Usage

### Using PrimeVue Components

PrimeVue components can be imported directly:

```vue
<script setup lang="ts">
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
</script>

<template>
    <Card>
        <template #title>My Card</template>
        <template #content>
            <InputText v-model="value" placeholder="Enter text..." />
            <Button label="Submit" icon="pi pi-check" />
        </template>
    </Card>
</template>
```

### Using Flowbite Components

Flowbite components are imported from `flowbite-vue`:

```vue
<script setup lang="ts">
import { FwbButton, FwbCard, FwbInput } from 'flowbite-vue';
</script>

<template>
    <FwbCard>
        <template #header>
            <h3>My Card</h3>
        </template>
        <template #default>
            <FwbInput v-model="value" placeholder="Enter text..." />
            <FwbButton color="blue">Submit</FwbButton>
        </template>
    </FwbCard>
</template>
```

### Using Both Libraries Together

You can use both libraries in the same component:

```vue
<script setup lang="ts">
import Button from 'primevue/button';
import { FwbButton } from 'flowbite-vue';
</script>

<template>
    <div class="flex gap-4">
        <Button label="PrimeVue Button" />
        <FwbButton color="green">Flowbite Button</FwbButton>
    </div>
</template>
```

## PrimeIcons

PrimeIcons are available for use with PrimeVue components:

```vue
<Button icon="pi pi-check" label="Save" />
<Button icon="pi pi-times" label="Cancel" />
```

See the [PrimeIcons documentation](https://primeng.org/icons) for all available icons.

## Dark Mode Support

Both libraries support dark mode. The application automatically applies dark mode based on the user's preference (configured in `useAppearance` composable).

- PrimeVue: Automatically switches themes based on the `.dark` class
- Flowbite: Uses Tailwind's dark mode classes

## Available Components

### PrimeVue Components

- Buttons, Cards, Inputs, Dialogs, DataTables, Charts, and many more
- See [PrimeVue Documentation](https://primevue.org/) for the complete list

### Flowbite Components

- Buttons, Cards, Forms, Modals, Dropdowns, Navigation, and more
- See [Flowbite Vue Documentation](https://flowbite-vue.com/) for the complete list

## Example Component

See `resources/js/components/examples/ComponentExamples.vue` for a complete example demonstrating both libraries.

## Styling

- **PrimeVue**: Uses its own theme system (Aura preset)
- **Flowbite**: Uses Tailwind CSS classes
- Both work together without conflicts

## Tips

1. **Consistency**: Choose one library as primary for consistency, use the other for specific components not available in the primary library
2. **Styling**: Flowbite components can be customized with Tailwind classes, while PrimeVue uses its theme system
3. **Icons**: Use PrimeIcons with PrimeVue components, and Lucide icons (already installed) with Flowbite components
4. **Performance**: Import only the components you need to keep bundle size small

## Resources

- [PrimeVue Documentation](https://primevue.org/)
- [Flowbite Vue Documentation](https://flowbite-vue.com/)
- [PrimeIcons](https://primeng.org/icons)
- [Tailwind CSS v4 Documentation](https://tailwindcss.com/docs)

