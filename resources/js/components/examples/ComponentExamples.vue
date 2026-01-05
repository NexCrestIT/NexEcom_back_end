<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import { FwbButton, FwbCard, FwbInput } from 'flowbite-vue';

// Example state
const inputValue = ref('');
const primeVueMessage = ref('PrimeVue is working!');
const flowbiteMessage = ref('Flowbite is working!');
</script>

<template>
    <div class="container mx-auto p-6 space-y-8">
        <h1 class="text-3xl font-bold mb-6">Component Library Examples</h1>

        <!-- PrimeVue Examples -->
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">PrimeVue Components</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- PrimeVue Card -->
                <Card>
                    <template #title>PrimeVue Card</template>
                    <template #content>
                        <p class="mb-4">{{ primeVueMessage }}</p>
                        <Button 
                            label="Click Me" 
                            icon="pi pi-check" 
                            @click="primeVueMessage = 'Button clicked!'"
                        />
                    </template>
                </Card>

                <!-- PrimeVue Input -->
                <Card>
                    <template #title>PrimeVue Input</template>
                    <template #content>
                        <div class="space-y-4">
                            <InputText 
                                v-model="inputValue" 
                                placeholder="Enter text..."
                                class="w-full"
                            />
                            <p>Value: {{ inputValue }}</p>
                        </div>
                    </template>
                </Card>
            </div>
        </section>

        <!-- Flowbite Examples -->
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Flowbite Components</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Flowbite Card -->
                <FwbCard>
                    <template #header>
                        <h3 class="text-xl font-semibold">Flowbite Card</h3>
                    </template>
                    <template #default>
                        <p class="mb-4">{{ flowbiteMessage }}</p>
                        <FwbButton 
                            color="blue"
                            @click="flowbiteMessage = 'Button clicked!'"
                        >
                            Click Me
                        </FwbButton>
                    </template>
                </FwbCard>

                <!-- Flowbite Input -->
                <FwbCard>
                    <template #header>
                        <h3 class="text-xl font-semibold">Flowbite Input</h3>
                    </template>
                    <template #default>
                        <div class="space-y-4">
                            <FwbInput 
                                v-model="inputValue" 
                                placeholder="Enter text..."
                            />
                            <p>Value: {{ inputValue }}</p>
                        </div>
                    </template>
                </FwbCard>
            </div>
        </section>

        <!-- Combined Usage Example -->
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold">Combined Usage</h2>
            <Card>
                <template #title>Using Both Libraries Together</template>
                <template #content>
                    <div class="space-y-4">
                        <p>You can use PrimeVue and Flowbite components together in the same page!</p>
                        <div class="flex gap-4">
                            <Button label="PrimeVue Button" />
                            <FwbButton color="green">Flowbite Button</FwbButton>
                        </div>
                    </div>
                </template>
            </Card>
        </section>
    </div>
</template>

