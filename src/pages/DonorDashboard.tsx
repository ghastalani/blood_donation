import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Layout } from '@/components/layout/Layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import { api } from '@/lib/api';
import { useCities } from '@/hooks/useCities';
import { Loader2, Check, X, Clock, Droplets, Calendar, ArrowLeft } from 'lucide-react';

interface DonationRequest {
  id: string;
  requester_id: string;
  status: 'pending' | 'accepted' | 'rejected';
  message: string | null;
  created_at: string;
  requester: {
    id: string;
    name: string;
    phone: string;
    email: string;
    city_id: string;
  };
}

const DonorDashboard = () => {
  const { t, dir, language } = useLanguage();
  const { profile, loading: authLoading, refreshProfile } = useAuth();
  const { cities } = useCities();
  const navigate = useNavigate();
  const { toast } = useToast();

  const [requests, setRequests] = useState<DonationRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [processingId, setProcessingId] = useState<string | null>(null);

  const fetchRequests = async () => {
    if (!profile) return;

    try {
      const data = await api.requests.get(profile.id, 'donor');
      // The API returns 'other_name', 'other_phone', etc. for the requester
      const formattedData = data.map((r: any) => ({
        ...r,
        requester: {
          id: r.requester_id,
          name: r.other_name,
          phone: r.other_phone,
          email: r.other_email,
          city_id: r.city_id || r.requester_city_id // Adjust based on API response
        }
      }));
      setRequests(formattedData || []);
    } catch (err) {
      console.error('Error fetching requests:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (!authLoading && !profile) {
      navigate('/login');
    } else if (profile && profile.role !== 'donor') {
      navigate('/');
    } else if (profile) {
      fetchRequests();
      // Manual refresh interval as a simple replacement for real-time
      const interval = setInterval(fetchRequests, 30000);
      return () => clearInterval(interval);
    }
  }, [profile, authLoading]);

  const handleRequest = async (requestId: string, action: 'accepted' | 'rejected') => {
    setProcessingId(requestId);
    try {
      if (action === 'accepted' && profile) {
        const response = await api.requests.acceptRequest(requestId, profile.id);
        if (response.status === 'success') {
          // Immediately update local state to reflect acceptance and rejection of others
          setRequests(prev => prev.map(req => {
            if (req.id === requestId) return { ...req, status: 'accepted' };
            if (req.status === 'pending') return { ...req, status: 'rejected' };
            return req;
          }));

          await refreshProfile();

          toast({
            title: dir === 'rtl' ? 'تم قبول الطلب بنجاح' : 'Request Accepted Successfully',
            description: dir === 'rtl'
              ? 'لقد وافقت على التبرع بالدم. سيتم تزويد طالب الدم بمعلومات الاتصال الخاصة بك.'
              : 'You have agreed to donate blood. The requester will be provided with your contact info.',
            className: "bg-success text-white border-none shadow-lg",
          });
        }
      } else {
        await api.requests.updateStatus(requestId, action);

        // Update local state
        setRequests(prev => prev.map(req =>
          req.id === requestId ? { ...req, status: action } : req
        ));

        toast({
          title: dir === 'rtl' ? 'تم تحديث الحالة' : 'Status Updated',
          description: action === 'accepted' ? t('requestAccepted') : t('requestRejected'),
          className: action === 'rejected' ? "bg-destructive text-white border-none" : "bg-success text-white border-none",
        });
      }

      // Re-fetch everything to be sure
      fetchRequests();
    } catch (err: any) {
      console.error('Error handling request:', err);
      toast({
        variant: 'destructive',
        title: dir === 'rtl' ? 'خطأ' : 'Error',
        description: err.message || t('genericError'),
      });
    } finally {
      setProcessingId(null);
    }
  };

  const getCityName = (cityId: string) => {
    if (!cities || !cityId) return '';
    const city = cities.find((c) => c.id === cityId);
    return city ? (language === 'ar' ? city.name_ar : city.name_en) : '';
  };

  const [timeLeft, setTimeLeft] = useState<{
    days: number;
    hours: number;
    minutes: number;
    seconds: number;
  } | null>(null);

  const calculateTimeLeft = () => {
    if (!profile?.cooldown_end_date) return null;
    const end = new Date(profile.cooldown_end_date);
    const now = new Date();
    const diff = end.getTime() - now.getTime();

    if (diff <= 0) {
      if (!profile.is_available) {
        refreshProfile(); // Trigger profile refresh when cooldown ends
      }
      return null;
    }

    return {
      days: Math.floor(diff / (1000 * 60 * 60 * 24)),
      hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
      minutes: Math.floor((diff / 1000 / 60) % 60),
      seconds: Math.floor((diff / 1000) % 60),
    };
  };

  useEffect(() => {
    if (profile?.cooldown_end_date && !profile.is_available) {
      const timer = setInterval(() => {
        const remaining = calculateTimeLeft();
        setTimeLeft(remaining);
        if (!remaining) clearInterval(timer);
      }, 1000);
      return () => clearInterval(timer);
    } else {
      setTimeLeft(null);
    }
  }, [profile?.cooldown_end_date, profile?.is_available]);

  const getDaysRemaining = () => {
    if (!profile?.cooldown_end_date) return 0;
    const end = new Date(profile.cooldown_end_date);
    const now = new Date();
    const diff = Math.ceil((end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return Math.max(0, diff);
  };

  if (authLoading || loading) {
    return (
      <Layout>
        <div className="min-h-[60vh] flex items-center justify-center">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="container mx-auto px-4 py-8">
        {/* Back Button */}
        <div className="mb-6">
          <Button
            variant="ghost"
            onClick={() => navigate(-1)}
            className="group flex items-center gap-2 text-muted-foreground hover:text-foreground transition-all duration-300 rounded-full px-4"
          >
            <ArrowLeft className={`h-4 w-4 transition-transform group-hover:${dir === 'rtl' ? 'translate-x-1' : '-translate-x-1'}`} />
            <span className="font-medium">
              {dir === 'rtl' ? 'العودة' : 'Back'}
            </span>
          </Button>
        </div>

        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">
            {t('welcomeBack')}, {profile?.name}
          </h1>
          <div className="flex items-center gap-4">
            <Badge variant={profile?.is_available ? 'default' : 'secondary'} className="text-sm">
              <Droplets className="h-4 w-4 mr-1" />
              {profile?.blood_type}
            </Badge>
            {profile?.is_available ? (
              <Badge variant="outline" className="bg-success/10 text-success border-success">
                {t('available')}
              </Badge>
            ) : (
              <Badge variant="outline" className="bg-warning/10 text-warning border-warning">
                <Clock className="h-4 w-4 mr-1" />
                {t('unavailable')} - {timeLeft ? `${timeLeft.days}d ${timeLeft.hours}h` : `${getDaysRemaining()} ${t('daysRemaining')}`}
              </Badge>
            )}
          </div>
        </div>

        {/* Cooldown Card */}
        {!profile?.is_available && (
          <Card className="mb-8 border-warning bg-warning/5 overflow-hidden relative shadow-lg">
            <div className="absolute top-0 left-0 w-2 h-full bg-warning" />
            <CardContent className="flex flex-col md:flex-row items-center gap-6 p-6">
              <div className="bg-warning/10 p-4 rounded-full">
                <Calendar className="h-8 w-8 text-warning" />
              </div>
              <div className="flex-1">
                <h3 className="text-xl font-bold text-foreground mb-1">
                  {dir === 'rtl' ? 'فترة الانتظار (90 يوماً)' : 'Cooldown Period (90 Days)'}
                </h3>
                <p className="text-muted-foreground">
                  {dir === 'rtl'
                    ? `لقد تبرعت مؤخراً. ستتمكن من التبرع مرة أخرى بعد:`
                    : `You have donated recently. You will be eligible to donate again in:`}
                </p>

                {/* Countdown Timer */}
                <div className="flex gap-4 mt-4">
                  {[
                    { label: dir === 'rtl' ? 'يوم' : 'Days', value: timeLeft?.days ?? 0 },
                    { label: dir === 'rtl' ? 'ساعة' : 'Hours', value: timeLeft?.hours ?? 0 },
                    { label: dir === 'rtl' ? 'دقيقة' : 'Mins', value: timeLeft?.minutes ?? 0 },
                    { label: dir === 'rtl' ? 'ثانية' : 'Secs', value: timeLeft?.seconds ?? 0 },
                  ].map((unit, i) => (
                    <div key={i} className="flex flex-col items-center">
                      <div className="bg-background border border-warning/20 rounded-lg w-16 h-16 flex items-center justify-center text-2xl font-bold text-warning shadow-sm">
                        {String(unit.value).padStart(2, '0')}
                      </div>
                      <span className="text-[10px] uppercase tracking-tighter mt-1 font-bold text-muted-foreground">
                        {unit.label}
                      </span>
                    </div>
                  ))}
                </div>

                <div className="mt-6 w-full bg-muted rounded-full h-2 overflow-hidden">
                  <div
                    className="bg-warning h-2 rounded-full transition-all duration-500 ease-out"
                    style={{ width: `${Math.min(100, (getDaysRemaining() / 90) * 100)}%` }}
                  />
                </div>
              </div>
              <div className="bg-warning/10 px-6 py-4 rounded-2xl text-center min-w-[120px]">
                <span className="text-5xl font-black text-warning leading-none">
                  {timeLeft?.days ?? getDaysRemaining()}
                </span>
                <p className="text-xs uppercase tracking-widest text-warning/80 font-bold mt-1">
                  {t('daysRemaining')}
                </p>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Requests */}
        <Card>
          <CardHeader>
            <CardTitle>{t('incomingRequests')}</CardTitle>
            <CardDescription>
              {dir === 'rtl'
                ? 'طلبات التبرع الواردة من الباحثين عن الدم'
                : 'Incoming donation requests from blood seekers'}
            </CardDescription>
          </CardHeader>
          <CardContent>
            {requests.length === 0 ? (
              <div className="text-center py-12 text-muted-foreground">
                {t('noRequests')}
              </div>
            ) : (
              <div className="space-y-4">
                {requests.map((request) => (
                  <Card key={request.id} className="glass-card">
                    <CardContent className="p-4">
                      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                          <h4 className="font-semibold text-foreground">
                            {request.requester.name}
                          </h4>
                          <p className="text-sm text-muted-foreground">
                            {getCityName(request.requester.city_id)}
                          </p>
                          <p className="text-xs text-muted-foreground mt-1">
                            {new Date(request.created_at).toLocaleDateString(language === 'ar' ? 'ar-SA' : 'en-US')}
                          </p>
                          {request.message && (
                            <p className="text-sm text-muted-foreground mt-2 italic">
                              "{request.message}"
                            </p>
                          )}
                        </div>

                        <div className="flex items-center gap-2">
                          {request.status === 'pending' ? (
                            profile?.is_available ? (
                              <>
                                <Button
                                  size="sm"
                                  onClick={() => handleRequest(request.id, 'accepted')}
                                  disabled={processingId === request.id}
                                  className="bg-success hover:bg-success/90"
                                >
                                  {processingId === request.id ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                  ) : (
                                    <Check className="h-4 w-4 mr-1" />
                                  )}
                                  {t('accept')}
                                </Button>
                                <Button
                                  size="sm"
                                  variant="destructive"
                                  onClick={() => handleRequest(request.id, 'rejected')}
                                  disabled={processingId === request.id}
                                >
                                  <X className="h-4 w-4 mr-1" />
                                  {t('reject')}
                                </Button>
                              </>
                            ) : (
                              <Badge variant="secondary">{t('unavailable')}</Badge>
                            )
                          ) : (
                            <Badge
                              variant={request.status === 'accepted' ? 'default' : 'destructive'}
                              className={request.status === 'accepted' ? 'bg-success' : ''}
                            >
                              {request.status === 'accepted' ? t('accepted') : t('rejected')}
                            </Badge>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
};

export default DonorDashboard;
