import React, { useState, createContext } from 'react'
import { hot } from 'react-hot-loader'
import { ThemeProvider } from "styled-components"
import { ToastProvider } from "react-toast-notifications"

import Modal from './Modal'
import Table from './Table'
import Toast from './Toast'
import { getScrollBarWidth } from "./helpers"

export const AppContext = createContext({})

const theme = {
  textColor: "#32373c",
  borderColor: "#ccd0d4",
  darkBorderColor: "#7e8993",
  tableHeight: "calc(100vh - 200px)",
  tableHeaderHeight: 40,
  tableBodyCellHeight: 60,
  cellPaddingH: 10,
  cellPaddingV: 8,
  scrollBarWidth: getScrollBarWidth()
}

function App({ data: initData }) {

  const [ modal, setModal ] = useState({ open: false, content: null })
  const [ data, setData ] = useState(initData)

  function openModal(content) {
    setModal({ open: true, content })
  }

  function closeModal() {
    setModal({ open: false, content: null })
  }

  return (
    <AppContext.Provider value={{ openModal, closeModal, data, setData }}>
      <ThemeProvider theme={theme}>
        <ToastProvider components={{ Toast: Toast }} autoDismiss={true} autoDismissTimeout={2000}>
          <Modal isOpen={modal.open}>
            { modal.content ? modal.content() : '' }
          </Modal>
          <Table data={data}/>
        </ToastProvider>
      </ThemeProvider>
    </AppContext.Provider>
  )
}

export default hot(module)(App)