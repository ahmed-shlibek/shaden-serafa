<!--<template>-->
<!--  <Card class="flex flex-col items-center justify-center">-->
<!--    <div class="px-3 py-3">-->
<!--      <h1 class="text-center text-3xl text-gray-500 font-light">Purchases Summary</h1>-->
<!--    </div>-->
<!--  </Card>-->
<!--</template>-->

<!--<script>-->
<!--export default {-->
<!--  props: [-->
<!--    'card',-->

<!--    // The following props are only available on resource detail cards...-->
<!--    // 'resource',-->
<!--    // 'resourceId',-->
<!--    // 'resourceName',-->
<!--  ],-->

<!--  mounted() {-->
<!--    //-->
<!--  },-->
<!--}-->
<!--</script>-->

<template>
    <div class="px-3 py-3">
        <h3 class="text-base font-bold mb-3">طلبات الشراء</h3>

        <div class="flex gap-2 mb-3">
            <button class="btn" :class="{active: tab==='type'}" @click="setTab('type')">حسب النوع</button>
            <button class="btn" :class="{active: tab==='payment'}" @click="setTab('payment')">حسب نوع الدفع</button>
            <button class="btn" :class="{active: tab==='cbl'}" @click="setTab('cbl')">حسب حالة CBL</button>
        </div>

        <div class="relative flex items-center justify-center">
            <canvas ref="chart" style="max-height: 320px;"></canvas>

            <div v-if="loading" class="absolute inset-0 flex items-center justify-center text-gray-500">
                Loading...
            </div>
        </div>
    </div>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
    props: ['card'],
    data() {
        return {
            tab: 'type',
            loading: true,
            chart: null,
            labels: [],
            values: [],
        }
    },
    mounted() {
        this.fetch()
    },
    methods: {
        async setTab(tab) {
            this.tab = tab
            await this.fetch()
        },
        async fetch() {
            this.loading = true

            const res = await Nova.request().get('/nova-vendor/purchases-summary/data', {
                params: { tab: this.tab }
            })

            const rows = res.data.data || []
            this.labels = rows.map(r => r.label)
            this.values = rows.map(r => Number(r.value))

            await this.$nextTick()

            this.renderChart()
            this.loading = false
        },

        renderChart() {
            const canvas = this.$refs.chart
            if (!canvas) return

            if (this.chart) this.chart.destroy()

            const ctx = canvas.getContext('2d')
            this.chart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: this.labels,
                    datasets: [{ data: this.values }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                },
            })
        },
    },
}
</script>

<style scoped>
.btn{
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    font-size: 13px;
}
.btn.active{
    border-color: #3b82f6;
}
</style>
