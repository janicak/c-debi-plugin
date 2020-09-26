import React from 'react'
import ReactDOM from 'react-dom'
import App from './App'

const root = document.getElementById('react-root')
const initData = window.c_debi_plugin.data.data;

ReactDOM.render(<App data={initData} />, root);