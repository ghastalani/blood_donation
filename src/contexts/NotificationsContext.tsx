import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { api } from '@/lib/api';
import { useAuth } from '@/contexts/AuthContext';

interface Notification {
    id: string;
    user_id: string;
    title_en: string;
    title_ar: string;
    message_en: string;
    message_ar: string;
    type: string;
    is_read: boolean;
    related_request_id: string | null;
    created_at: string;
}

interface NotificationsContextType {
    notifications: Notification[];
    loading: boolean;
    unreadCount: number;
    markAsRead: (notificationId: string) => Promise<void>;
    markAllAsRead: () => Promise<void>;
    refetch: () => Promise<void>;
}

const NotificationsContext = createContext<NotificationsContextType | undefined>(undefined);

export const NotificationsProvider = ({ children }: { children: ReactNode }) => {
    const { profile } = useAuth();
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [loading, setLoading] = useState(true);

    const fetchNotifications = async () => {
        if (!profile) {
            setNotifications([]);
            setLoading(false);
            return;
        }

        try {
            const data = await api.notifications.get(profile.id);
            setNotifications(data || []);
        } catch (err) {
            console.error('Error fetching notifications:', err);
        } finally {
            setLoading(false);
        }
    };

    const markAsRead = async (notificationId: string) => {
        try {
            await api.notifications.markRead(notificationId);
            setNotifications((prev) =>
                prev.map((n) => (n.id === notificationId ? { ...n, is_read: true } : n))
            );
        } catch (err) {
            console.error('Error marking notification as read:', err);
        }
    };

    const markAllAsRead = async () => {
        if (!profile) return;

        try {
            const unreadNotifications = notifications.filter(n => !n.is_read);
            await Promise.all(unreadNotifications.map(n => api.notifications.markRead(n.id)));
            setNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
        } catch (err) {
            console.error('Error marking all as read:', err);
        }
    };

    useEffect(() => {
        if (profile) {
            fetchNotifications();
            const interval = setInterval(fetchNotifications, 10000); // Poll every 10 seconds
            return () => clearInterval(interval);
        } else {
            setNotifications([]);
            setLoading(false);
        }
    }, [profile]);

    const unreadCount = notifications.filter((n) => !n.is_read).length;

    return (
        <NotificationsContext.Provider value={{
            notifications,
            loading,
            unreadCount,
            markAsRead,
            markAllAsRead,
            refetch: fetchNotifications,
        }}>
            {children}
        </NotificationsContext.Provider>
    );
};

export const useNotifications = () => {
    const context = useContext(NotificationsContext);
    if (!context) {
        throw new Error('useNotifications must be used within a NotificationsProvider');
    }
    return context;
};
