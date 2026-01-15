// const API_URL = '/api';
// const SUPABASE_FUNCTION_URL = 'https://iqhgwynhisbkjeictqjm.supabase.co/functions/v1/resend-email';


// async function fetchAPI(endpoint: string, options: RequestInit = {}) {
//     const url = `${API_URL}${endpoint}`;
//     try {
//         const response = await fetch(url, {
//             ...options,
//             headers: {
//                 'Content-Type': 'application/json',
//                 ...options.headers,
//             },
//         });

//         const text = await response.text();
//         let data;
//         try {
//             data = JSON.parse(text);
//         } catch (e) {
//             console.error(`Failed to parse API response from ${url} as JSON. Status: ${response.status}. Body preview: ${text.substring(0, 100)}`);
//             throw new Error(`Invalid API response from ${url} (Status: ${response.status})`);
//         }

//         if (!response.ok) {
//             throw new Error(data.message || `API Error: ${response.status}`);
//         }
//         return data;
//     } catch (error: any) {
//         console.error(`API Fetch Error (${url}):`, error);
//         throw error;
//     }
// }

// export const api = {
//     auth: {
//         signup: (email: string, password: string, metadata: any) =>
//             fetchAPI('/auth.php?action=signup', {
//                 method: 'POST',
//                 body: JSON.stringify({ email, password, metadata }),
//             }),
//         signin: (email: string, password: string) =>
//             fetchAPI('/auth.php?action=signin', {
//                 method: 'POST',
//                 body: JSON.stringify({ email, password }),
//             }),
//     },
//     cities: {
//         getAll: () => fetchAPI('/cities.php'),
//     },
//     profiles: {
//         get: (userId: string) => fetchAPI(`/profiles.php?action=get_profile&userId=${userId}`),
//         search: (bloodType?: string, cityId?: string) =>
//             fetchAPI(`/profiles.php?action=search_donors&bloodType=${bloodType || ''}&cityId=${cityId || ''}`),
//         update: (data: any) =>
//             fetchAPI('/profiles.php?action=update_profile', {
//                 method: 'POST',
//                 body: JSON.stringify(data),
//             }),
//     },
//     requests: {
//         get: (profileId: string, role: string) =>
//             fetchAPI(`/requests.php?action=get_requests&profileId=${profileId}&role=${role}`),
//         create: (data: any) =>
//             fetchAPI('/requests.php?action=create_request', {
//                 method: 'POST',
//                 body: JSON.stringify(data),
//             }),
//         updateStatus: (id: string, status: string) =>
//             fetchAPI('/requests.php?action=update_status', {
//                 method: 'POST',
//                 body: JSON.stringify({ id, status }),
//             }),
//         acceptRequest: (id: string, donor_id: string) =>
//             fetchAPI('/requests.php?action=accept_request', {
//                 method: 'POST',
//                 body: JSON.stringify({ id, donor_id }),
//             }),
//     },
//     notifications: {
//         get: (userId: string) => fetchAPI(`/notifications.php?userId=${userId}`),
//         create: (data: any) =>
//             fetchAPI('/notifications.php?action=create_notification', {
//                 method: 'POST',
//                 body: JSON.stringify(data),
//             }),
//         markRead: (id: string) =>
//             fetchAPI('/notifications.php?action=mark_read', {
//                 method: 'POST',
//                 body: JSON.stringify({ id }),
//             }),
//     },
//     contact: {
//         send: (data: any) =>
//             fetchAPI('/contact.php', {
//                 method: 'POST',
//                 body: JSON.stringify(data),
//             }),
//     },
//     admin: {
//         getStats: () => fetchAPI('/admin.php?action=get_stats'),
//         getUsers: () => fetchAPI('/admin.php?action=get_users'),
//         updateUser: (data: any) =>
//             fetchAPI('/admin.php?action=update_user', {
//                 method: 'POST',
//                 body: JSON.stringify(data),
//             }),
//     }
// };
const API_URL = '/api';
const SUPABASE_FUNCTION_URL = 'https://iqhgwynhisbkjeictqjm.supabase.co/functions/v1/resend-email';

async function fetchAPI(endpoint: string, options: RequestInit = {}) {
    const url = `${API_URL}${endpoint}`;
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error(`Failed to parse API response from ${url} as JSON. Status: ${response.status}. Body preview: ${text.substring(0, 100)}`);
            throw new Error(`Invalid API response from ${url} (Status: ${response.status})`);
        }

        if (!response.ok) {
            throw new Error(data.message || `API Error: ${response.status}`);
        }
        return data;
    } catch (error: any) {
        console.error(`API Fetch Error (${url}):`, error);
        throw error;
    }
}

export const api = {
    auth: {
        signup: (email: string, password: string, metadata: any) =>
            fetchAPI('/auth.php?action=signup', {
                method: 'POST',
                body: JSON.stringify({ email, password, metadata }),
            }),
        signin: (email: string, password: string) =>
            fetchAPI('/auth.php?action=signin', {
                method: 'POST',
                body: JSON.stringify({ email, password }),
            }),
    },
    cities: {
        getAll: () => fetchAPI('/cities.php'),
    },
    profiles: {
        get: (userId: string) => fetchAPI(`/profiles.php?action=get_profile&userId=${userId}`),
        search: (bloodType?: string, cityId?: string) =>
            fetchAPI(`/profiles.php?action=search_donors&bloodType=${encodeURIComponent(bloodType || '')}&cityId=${encodeURIComponent(cityId || '')}`),
        update: (data: any) =>
            fetchAPI('/profiles.php?action=update_profile', {
                method: 'POST',
                body: JSON.stringify(data),
            }),
    },
    requests: {
        get: (profileId: string, role: string) =>
            fetchAPI(`/requests.php?action=get_requests&profileId=${profileId}&role=${role}`),

        // ✅ تعديل create لتستخدم Supabase Edge Function لإرسال الإيميل
        create: (data: any) =>
            fetchAPI('/requests.php?action=create_request', {
                method: 'POST',
                body: JSON.stringify(data),
            }),

        updateStatus: (id: string, status: string) =>
            fetchAPI('/requests.php?action=update_status', {
                method: 'POST',
                body: JSON.stringify({ id, status }),
            }),
        acceptRequest: (id: string, donor_id: string) =>
            fetchAPI('/requests.php?action=accept_request', {
                method: 'POST',
                body: JSON.stringify({ id, donor_id }),
            }),
    },
    notifications: {
        get: (userId: string) => fetchAPI(`/notifications.php?userId=${userId}`),
        create: (data: any) =>
            fetchAPI('/notifications.php?action=create_notification', {
                method: 'POST',
                body: JSON.stringify(data),
            }),
        markRead: (id: string) =>
            fetchAPI('/notifications.php?action=mark_read', {
                method: 'POST',
                body: JSON.stringify({ id }),
            }),
    },
    contact: {
        send: (data: any) =>
            fetchAPI('/contact.php', {
                method: 'POST',
                body: JSON.stringify(data),
            }),
    },
    admin: {
        getStats: () => fetchAPI('/admin.php?action=get_stats'),
        getUsers: () => fetchAPI('/admin.php?action=get_users'),
        updateUser: (data: any) =>
            fetchAPI('/admin.php?action=update_user', {
                method: 'POST',
                body: JSON.stringify(data),
            }),
    }
};
