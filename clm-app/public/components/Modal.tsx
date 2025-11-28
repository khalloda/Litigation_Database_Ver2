import React, { useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { XIcon } from './icons';

interface ModalProps {
    title: string;
    onClose: () => void;
    children: React.ReactNode;
}

const Modal: React.FC<ModalProps> = ({ title, onClose, children }) => {
    const { direction } = useI18n();

    useEffect(() => {
        const handleEsc = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };
        window.addEventListener('keydown', handleEsc);

        return () => {
            window.removeEventListener('keydown', handleEsc);
        };
    }, [onClose]);

    return (
        <div
            style={{
                position: 'fixed',
                inset: 0,
                backgroundColor: 'rgba(0,0,0,0.6)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                padding: '1rem',
                zIndex: 9999,
            }}
            onClick={onClose}
            dir={direction}
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
        >
            <div
                style={{
                    backgroundColor: '#fff',
                    borderRadius: '0.75rem',
                    boxShadow: '0 25px 50px rgba(0,0,0,0.25)',
                    padding: '1.5rem',
                    width: '100%',
                    maxWidth: '640px',
                    position: 'relative',
                }}
                onClick={e => e.stopPropagation()}
            >
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid #e5e7eb', paddingBottom: '0.75rem', marginBottom: '1.25rem' }}>
                    <h2 id="modal-title" style={{ fontSize: '1.25rem', fontWeight: 700, color: '#1f2937', margin: 0 }}>{title}</h2>
                    <button
                        onClick={onClose}
                        aria-label="Close"
                        style={{
                            border: 'none',
                            background: 'transparent',
                            color: '#9ca3af',
                            cursor: 'pointer',
                            padding: 0,
                        }}
                    >
                        <XIcon className="w-6 h-6" />
                    </button>
                </div>
                {children}
            </div>
        </div>
    );
};

export default Modal;