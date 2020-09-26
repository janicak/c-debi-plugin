import React, { useContext } from "react"
import ReactModal from "react-modal"
import styled from "styled-components"

import { AppContext } from "../App"

const rootId = "#react-root"
ReactModal.setAppElement(rootId)

const ReactModalAdapter = ({ className, ...props }) => {
  const modalClassName = `${className}__modal`
  const overlayClassName = `${className}__overlay`
  return (
    <ReactModal
      portalClassName={className}
      className={modalClassName}
      overlayClassName={overlayClassName}
      {...props}
    />
  )
}

const StyledModal = styled(ReactModalAdapter)`
  &__overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
  }
  &__modal {
    position: absolute;
    top: 50%;
    left: 50%;
    right: auto;
    bottom: auto;
    transform: translate(-50%, -50%);
    width: 80vw;
    max-width: 700px;
    min-height: 200px;
    max-height: 80vh;
    overflow: auto;
    box-shadow: 0px 0px 17px 5px rgba(0, 0, 0, 0.12);
    border: 1px solid ${props => props.theme.borderColor};
    background-color: white;
  }
  .content-container {
    padding: 2rem 2rem;
  }
`

const Modal = ({ isOpen, children }) => {
  const { closeModal } = useContext(AppContext)
  return (
    <StyledModal
      isOpen={isOpen}
      contentLabel="Person Detail Modal"
      parentSelector={() => document.querySelector(rootId)}
      onRequestClose={closeModal}
      shouldCloseOnOverlayClick={true}
    >
      <div className="content-container">
        {children}
      </div>
    </StyledModal>
  )
}

export default Modal