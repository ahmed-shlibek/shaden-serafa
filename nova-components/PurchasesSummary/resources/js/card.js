import Card from './components/Card'

Nova.booting((app, store) => {
  app.component('purchases-summary', Card)
})
