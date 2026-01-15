import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { ShieldAlert, LogOut } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

const Banned = () => {
    const { t, dir } = useLanguage();
    const { signOut } = useAuth();
    const navigate = useNavigate();

    const handleSignOut = async () => {
        await signOut();
        navigate('/login');
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-background p-4" dir={dir}>
            <div className="max-w-md w-full text-center space-y-6 animate-in fade-in zoom-in duration-300">
                <div className="flex justify-center">
                    <div className="p-4 rounded-full bg-destructive/10">
                        <ShieldAlert className="h-16 w-16 text-destructive" />
                    </div>
                </div>

                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight text-foreground">
                        {dir === 'rtl' ? 'تم حظر حسابك' : 'Account Banned'}
                    </h1>
                    <p className="text-muted-foreground text-lg">
                        {dir === 'rtl'
                            ? 'حسابك محظور. يرجى الاتصال بالمسؤول لمزيد من المعلومات.'
                            : 'Your account is banned. Please contact the admin for more information.'}
                    </p>
                </div>

                <div className="pt-4">
                    <Button
                        variant="outline"
                        onClick={handleSignOut}
                        className="w-full flex items-center justify-center gap-2"
                    >
                        <LogOut className="h-4 w-4" />
                        {dir === 'rtl' ? 'تسجيل الخروج' : 'Sign Out'}
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default Banned;
