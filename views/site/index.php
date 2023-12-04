<?php

/** @var yii\web\View $this */

use yii\web\View;

$this->title = 'My Yii Application';
?>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="app" class="site-index">
    <h2 class="text-center">Módulo de Pedidos</h2>
    <form @submit.prevent="procesarFormulario" class="row mt-5 justify-content-start">
        <div class="col-5 col-md-4">
            <div class="row">
                <div class="col">
                    <label class="form-label">Fecha inicio:</label>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <input 
                        type="date" 
                        name="fechaInicio"
                        v-model="filtro.inicio"
                        required
                        class="form-control">
                </div>
            </div>
        </div>
        <div class="col-5 col-md-4">
            <div class="row">
                <div class="col">
                    <label class="form-label">Fecha fin:</label>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <input 
                        type="date" 
                        v-model="filtro.fin"
                        required
                        class="form-control">
                </div>
            </div>
        </div>
        <div class="col d-flex align-items-center justify-content-center mt-1">
            <button 
                type="submit"
                :disabled="cargando"
                class="btn btn-primary">Filtrar</button>
            <button 
                type="button"
                @click="resetear()"
                :disabled="cargando"
                class="btn btn-primary ml-2">Resetear</button>
        </div>
    </form>
    <div class="row mt-2">
        <div class="col">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody v-show="!cargando">
                    <tr v-for="i in pedidos">
                        <td>{{i.id}}</td>
                        <td>{{i.cliente.nombre}}</td>
                        <td>{{formatearFecha(i.fecha_registro)}}</td>
                        <td>${{i.subtotal}}</td>
                        <td>${{i.iva}}</td>
                        <td>${{i.total}}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="cargando" class="col mt-3 d-flex justify-content-center">
                <div class="spinner-grow" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const { createApp, ref, reactive } = Vue
    createApp({
        setup() {
            const cargando = ref(false)
            const pedidos = ref([])
            const filtro = reactive({
                inicio: '',
                fin: '',
            })

            const formatearFecha=(fecha)=>{
                return new Date(fecha.replace(" ","T")+'Z').toLocaleString()
            }
            const procesarFormulario = async()=>{
                cargando.value = true;
                const respuesta = await fetch(`/api/pedidos?inicio=${filtro.inicio}&fin=${filtro.fin}`)
                pedidos.value = await respuesta.json()
                cargando.value = false
            }
            const resetear = ()=>{
                filtro.inicio = '';
                filtro.fin = '';
                cargarPedidos();
            }

            const cargarPedidos = async()=>{
                cargando.value = true;
                const respuesta = await fetch('/api/pedidos')
                pedidos.value = await respuesta.json()
                cargando.value = false
            }
            return {
                pedidos,
                cargando,
                formatearFecha,
                procesarFormulario,
                filtro,
                cargarPedidos,
                resetear,
            }
        },
        mounted() {
            this.cargarPedidos()
        }
    }).mount('#app')
</script>

