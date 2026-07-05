<template>
    <div class="calculator">
  
      <!-- HEADER -->
      <div class="calc-top d-flex justify-content-between align-items-center">
        <span>Calculator</span>
        <button class="btn btn-sm btn-light" @click="toggleMode">
          {{ isScientific ? 'Basic' : 'Scientific' }}
        </button>
      </div>
  
      <!-- DISPLAY -->
      <div class="display">
        <div class="expression">{{ expression || '0' }}</div>
        <div class="result">= {{ result }}</div>
      </div>
  
      <div class="calc-body">
  
        <!-- BUTTONS -->
        <div class="buttons">
  
          <!-- Scientific (only when enabled) -->
          <template v-if="isScientific">
            <button @click="append('sqrt(')">√</button>
            <button @click="append('^')">^</button>
            <button @click="append('pi')">π</button>
            <button @click="append('(')">(</button>
  
            <button @click="append('sin(')">sin</button>
            <button @click="append('cos(')">cos</button>
            <button @click="append('tan(')">tan</button>
            <button @click="append(')')">)</button>
          </template>
  
          <!-- Basic -->
          <button @click="clear">AC</button>
          <button @click="backspace">⌫</button>
          <button @click="append('%')">%</button>
          <button @click="append('/')">÷</button>
  
          <button @click="append('7')">7</button>
          <button @click="append('8')">8</button>
          <button @click="append('9')">9</button>
          <button @click="append('*')">×</button>
  
          <button @click="append('4')">4</button>
          <button @click="append('5')">5</button>
          <button @click="append('6')">6</button>
          <button @click="append('-')">−</button>
  
          <button @click="append('1')">1</button>
          <button @click="append('2')">2</button>
          <button @click="append('3')">3</button>
          <button @click="append('+')">+</button>
  
          <button class="zero" @click="append('0')">0</button>
          <button @click="append('.')">.</button>
          <button class="equal" @click="calculate">=</button>
        </div>
  
        <!-- HISTORY -->
 <div 
  v-for="(item, index) in history" 
  :key="index" 
  class="history-item"
  @click="useHistory(item)"
>
  {{ item }}
</div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue'
  import { evaluate } from 'mathjs'
  
  const expression = ref('')
  const history = ref([])
  const isScientific = ref(false)
  
  const toggleMode = () => {
    isScientific.value = !isScientific.value
  }
  const parsePercentage = (exp) => {
  // convert 50% → (50/100)
  return exp.replace(/(\d+)%/g, '($1/100)')
}

const useHistory = (item) => {
  expression.value = item.split('=')[0].trim()
}

const result = computed(() => {
  try {
    if (!expression.value) return 0

    if (/[+\-*/.^%]$/.test(expression.value)) return '...'

    const parsed = parsePercentage(expression.value)

    return evaluate(parsed)
  } catch {
    return '...'
  }
})
  
  const append = (val) => {
    // prevent double operators
    if (/[+\-*/.]$/.test(expression.value) && /[+\-*/.]/.test(val)) return
    expression.value += val
  }
  
  const clear = () => {
    expression.value = ''
  }
  
  const backspace = () => {
    expression.value = expression.value.slice(0, -1)
  }
  
  const calculate = () => {
  if (result.value !== '...') {
    const record = `${expression.value} = ${result.value}`

    history.value.unshift(record)

    // ✅ save
    localStorage.setItem('calc_history', JSON.stringify(history.value))

    expression.value = String(result.value)
  }
}
  
  /* KEYBOARD SUPPORT */
  const handleKey = (e) => {
    const key = e.key
  
    if (!isNaN(key) || ['+', '-', '*', '/', '.', '(', ')', '^'].includes(key)) {
      append(key)
    }
  
    if (key === 'Enter') calculate()
    if (key === 'Backspace') backspace()
    if (key === 'Escape') clear()
  }
  
  onMounted(() => {
    window.addEventListener('keydown', handleKey)

  const saved = localStorage.getItem('calc_history')
  if (saved) {
    history.value = JSON.parse(saved)
  }
  })
  </script>
  
  <style scoped>
  .calculator {
    width: 100%;
    background: #111;
    color: white;
    border-radius: 10px;
    padding: 10px;
  }
  
  .calc-top {
    padding: 5px 10px;
  }
  
  .display {
    background: #222;
    padding: 15px;
    border-radius: 8px;
    text-align: right;
  }
  
  .expression {
    font-size: 14px;
    opacity: 0.7;
  }
  
  .result {
    font-size: 24px;
    font-weight: bold;
  }
  
  .calc-body {
    display: flex;
    gap: 10px;
    margin-top: 10px;
  }
  
  /* BUTTON GRID */
  .buttons {
    flex: 2;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }
  
  button {
    padding: 15px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    background: #333;
    color: white;
  }
  
  button:active {
    transform: scale(0.95);
  }
  
  .equal {
    background: #28a745;
  }
  
  .zero {
    grid-column: span 2;
  }
  
  /* HISTORY */
  .history {
    flex: 1;
    background: #1b1b1b;
    padding: 10px;
    border-radius: 8px;
    max-height: 300px;
    overflow-y: auto;
  }
  
  .history-item {
    font-size: 13px;
    border-bottom: 1px solid #444;
    padding: 5px 0;
  }
  
  /* MOBILE */
  @media (max-width: 768px) {
    .calc-body {
      flex-direction: column;
    }
  
    .history {
      max-height: 150px;
    }
  }
  </style>