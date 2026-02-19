<template>
    <div class="px-3 py-3 bg-gray-200">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-bold">معاملات الشراء</h3>
        </div>

        <div v-if="error" class="text-sm text-red-600 mb-2">
            {{ error }}
        </div>

        <div class="relative" style="height: 320px;">
            <canvas ref="chart"></canvas>

            <div
                v-if="loading"
                class="absolute inset-0 flex items-center justify-center text-gray-500"
                style="background: rgba(255,255,255,0.6);"
            >
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
            loading: true,
            error: null,
            labels: [],
            requested: [],
            approved: [],
            remaining: [],
            chart: null,
        }
    },

    mounted() {
        this.fetch()
    },

    beforeUnmount() {
        if (this.chart) this.chart.destroy()
    },

    methods: {
        async fetch() {
            this.loading = true
            this.error = null

            try {
                const res = await Nova.request().get('/nova-vendor/purchase-transactions/data')

                this.labels = res.data.labels || []
                this.requested = (res.data.requested || []).map(v => Number(v))
                this.approved  = (res.data.approved  || []).map(v => Number(v))
                this.remaining  = (res.data.remaining  || []).map(v => Number(v))

                await this.$nextTick()
                this.renderChart()
            } catch (e) {
                console.error(e)
                this.error = e?.response?.data?.message || e.message || 'Request failed'
            } finally {
                this.loading = false
            }
        },

        renderChart() {
            const canvas = this.$refs.chart
            if (!canvas) return

            if (this.chart) this.chart.destroy()

            this.chart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: this.labels,
                    datasets: [
                        {
                            label: 'ألإجمالي',
                            data: this.requested,
                            tension: 0.35,
                            fill: false,
                        },
                        {
                            label: 'المعتمد',
                            data: this.approved,
                            tension: 0.35,
                            fill: false,
                        },
                        {
                            label: 'الباقي',
                            data: this.remaining,
                            tension: 0.35,
                            fill: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: (item) => ` ${Number(item.raw || 0).toLocaleString()}`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: true },
                    },
                },
            })
        },
    },
}
</script>
