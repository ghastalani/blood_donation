import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Layout } from '@/components/layout/Layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useToast } from '@/hooks/use-toast';
import { api } from '@/lib/api';
import { useCities } from '@/hooks/useCities';
import { Loader2, Search, Shield, ShieldOff, Send, Mail, Droplets, MapPin, MessageSquare, Eye, FileText } from 'lucide-react';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

interface User {
  id: string;
  user_id: string;
  name: string;
  email: string;
  phone: string;
  role: 'donor' | 'requester' | 'admin';
  blood_type: string | null;
  city_id: string | null;
  is_available: boolean;
  is_banned: boolean;
}

interface ContactMessage {
  id: string;
  name: string;
  email: string;
  message: string;
  is_read: boolean;
  created_at: string;
}

const AdminDashboard = () => {
  const { t, dir, language } = useLanguage();
  const { profile, loading: authLoading } = useAuth();
  const { cities } = useCities();
  const navigate = useNavigate();
  const { toast } = useToast();

  const [users, setUsers] = useState<User[]>([]);
  const [messages, setMessages] = useState<ContactMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState<string | null>(null);

  // Filters
  const [nameFilter, setNameFilter] = useState('');
  const [emailFilter, setEmailFilter] = useState('');

  // Broadcast
  const [broadcastCity, setBroadcastCity] = useState<string>('');
  const [broadcastBloodType, setBroadcastBloodType] = useState<string>('');
  const [broadcastMessage, setBroadcastMessage] = useState('');
  const [sendingBroadcast, setSendingBroadcast] = useState(false);

  const fetchUsers = async () => {
    try {
      const data = await api.admin.getUsers();
      setUsers(data || []);
    } catch (err) {
      console.error('Error fetching users:', err);
    }
  };

  const fetchMessages = async () => {
    try {
      // Assuming api.contact.getMessages exists or adding it
      // For now using profiles.get (placeholder replacement)
      // Let's actually add getMessages to contact API
      const response = await fetch('/api/contact.php?action=get_messages');
      const data = await response.json();
      setMessages(data || []);
    } catch (err) {
      console.error('Error fetching messages:', err);
    }
  };

  useEffect(() => {
    if (!authLoading && !profile) {
      navigate('/login');
    } else if (profile && profile.role !== 'admin') {
      navigate('/');
    } else if (profile) {
      Promise.all([fetchUsers(), fetchMessages()]).then(() => setLoading(false));
    }
  }, [profile, authLoading]);

  const toggleBan = async (profileId: string, currentBanned: boolean) => {
    setProcessing(profileId);
    try {
      await api.admin.updateUser({ id: profileId, is_banned: !currentBanned });

      setUsers((prev) =>
        prev.map((u) => (u.id === profileId ? { ...u, is_banned: !currentBanned } : u))
      );

      toast({
        title: dir === 'rtl' ? 'نجاح' : 'Success',
        description: currentBanned
          ? (dir === 'rtl' ? 'تم إلغاء حظر المستخدم' : 'User unbanned')
          : (dir === 'rtl' ? 'تم حظر المستخدم' : 'User banned'),
      });
    } catch (err) {
      console.error('Error toggling ban:', err);
      toast({
        variant: 'destructive',
        title: dir === 'rtl' ? 'خطأ' : 'Error',
        description: t('genericError'),
      });
    } finally {
      setProcessing(null);
    }
  };

  const markMessageRead = async (messageId: string) => {
    try {
      await fetch('/api/contact.php?action=mark_read', {
        method: 'POST',
        body: JSON.stringify({ id: messageId })
      });

      setMessages((prev) =>
        prev.map((m) => (m.id === messageId ? { ...m, is_read: true } : m))
      );
    } catch (err) {
      console.error('Error marking message read:', err);
    }
  };

  const sendBroadcast = async () => {
    if (!broadcastMessage.trim()) {
      toast({
        variant: 'destructive',
        title: dir === 'rtl' ? 'خطأ' : 'Error',
        description: dir === 'rtl' ? 'يرجى إدخال رسالة البث' : 'Please enter a broadcast message',
      });
      return;
    }

    setSendingBroadcast(true);
    try {
      let targets: any[] = [];

      // If no filters are active, target ALL non-banned users
      if (!broadcastCity && (!broadcastBloodType || broadcastBloodType === 'all')) {
        targets = users.filter(u => !u.is_banned);
      } else {
        // Use filtered search
        targets = await api.profiles.search(broadcastBloodType === 'all' ? '' : broadcastBloodType, broadcastCity);
      }

      if (!targets || targets.length === 0) {
        toast({
          variant: 'destructive',
          title: dir === 'rtl' ? 'خطأ' : 'Error',
          description: dir === 'rtl' ? 'لم يتم العثور على مستخدمين لمطابقة المعايير' : 'No users found matching the criteria',
        });
        return;
      }

      // Send to all targets
      await Promise.all(targets.map((target: any) =>
        api.notifications.create({
          user_id: target.id,
          title_en: 'System Announcement',
          title_ar: 'إعلان من النظام',
          message_en: broadcastMessage,
          message_ar: broadcastMessage,
          type: 'broadcast',
        })
      ));

      toast({
        title: dir === 'rtl' ? 'تم إرسال البث' : 'Broadcast Sent',
        description: dir === 'rtl'
          ? `تم إرسال الرسالة بنجاح إلى ${targets.length} مستخدم.`
          : `Message successfully sent to ${targets.length} users.`,
        className: "bg-success text-white border-none",
      });

      setBroadcastMessage('');
      setBroadcastCity('');
      setBroadcastBloodType('');
    } catch (err) {
      console.error('Error sending broadcast:', err);
      toast({
        variant: 'destructive',
        title: dir === 'rtl' ? 'خطأ' : 'Error',
        description: t('genericError'),
      });
    } finally {
      setSendingBroadcast(false);
    }
  };

  const getCityName = (cityId: string | null) => {
    if (!cityId || !cities) return '-';
    const city = cities.find((c) => c.id === cityId);
    return city ? (language === 'ar' ? city.name_ar : city.name_en) : '-';
  };

  const filteredUsers = users.filter((user) => {
    const matchName = user.name.toLowerCase().includes(nameFilter.toLowerCase());
    const matchEmail = user.email.toLowerCase().includes(emailFilter.toLowerCase());
    return matchName && matchEmail;
  });

  const unreadMessagesCount = messages.filter((m) => !m.is_read).length;

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
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">
            {t('adminDashboard')}
          </h1>
          <p className="text-muted-foreground">
            {dir === 'rtl' ? 'إدارة المستخدمين والرسائل' : 'Manage users and messages'}
          </p>
        </div>

        <Tabs defaultValue="users" className="space-y-6">
          <TabsList>
            <TabsTrigger value="users">{t('allUsers')}</TabsTrigger>
            <TabsTrigger value="messages" className="relative">
              {t('contactMessages')}
              {unreadMessagesCount > 0 && (
                <Badge variant="destructive" className="ml-2 h-5 w-5 p-0 flex items-center justify-center text-xs">
                  {unreadMessagesCount}
                </Badge>
              )}
            </TabsTrigger>
            <TabsTrigger value="broadcast">{t('broadcastRequest')}</TabsTrigger>
          </TabsList>

          {/* Users Tab */}
          <TabsContent value="users">
            <Card>
              <CardHeader>
                <CardTitle>{t('allUsers')}</CardTitle>
                <CardDescription>
                  {dir === 'rtl' ? 'عرض وإدارة جميع المستخدمين' : 'View and manage all users'}
                </CardDescription>
              </CardHeader>
              <CardContent>
                {/* Filters */}
                <div className="flex flex-col sm:flex-row gap-4 mb-6">
                  <div className="relative flex-1">
                    <Search className={`absolute top-3 h-4 w-4 text-muted-foreground ${dir === 'rtl' ? 'right-3' : 'left-3'}`} />
                    <Input
                      placeholder={t('filterByName')}
                      value={nameFilter}
                      onChange={(e) => setNameFilter(e.target.value)}
                      className={dir === 'rtl' ? 'pr-10' : 'pl-10'}
                    />
                  </div>
                  <div className="relative flex-1">
                    <Mail className={`absolute top-3 h-4 w-4 text-muted-foreground ${dir === 'rtl' ? 'right-3' : 'left-3'}`} />
                    <Input
                      placeholder={t('filterByEmail')}
                      value={emailFilter}
                      onChange={(e) => setEmailFilter(e.target.value)}
                      className={dir === 'rtl' ? 'pr-10' : 'pl-10'}
                    />
                  </div>
                </div>

                {/* Users Table */}
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>{t('name')}</TableHead>
                        <TableHead>{t('email')}</TableHead>
                        <TableHead>{dir === 'rtl' ? 'الدور' : 'Role'}</TableHead>
                        <TableHead>{t('bloodType')}</TableHead>
                        <TableHead>{t('city')}</TableHead>
                        <TableHead>{dir === 'rtl' ? 'الحالة' : 'Status'}</TableHead>
                        <TableHead>{dir === 'rtl' ? 'الإجراءات' : 'Actions'}</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {filteredUsers.map((user) => (
                        <TableRow key={user.id}>
                          <TableCell className="font-medium">{user.name}</TableCell>
                          <TableCell>{user.email}</TableCell>
                          <TableCell>
                            <Badge variant="outline">
                              {user.role === 'donor'
                                ? t('donor')
                                : user.role === 'requester'
                                  ? t('requester')
                                  : 'Admin'}
                            </Badge>
                          </TableCell>
                          <TableCell>{user.blood_type || '-'}</TableCell>
                          <TableCell>{getCityName(user.city_id)}</TableCell>
                          <TableCell>
                            <Badge variant={user.is_banned ? 'destructive' : 'default'}>
                              {user.is_banned
                                ? (dir === 'rtl' ? 'محظور' : 'Banned')
                                : (dir === 'rtl' ? 'نشط' : 'Active')}
                            </Badge>
                          </TableCell>
                          <TableCell>
                            {user.role !== 'admin' && (
                              <Button
                                size="sm"
                                variant={user.is_banned ? 'default' : 'destructive'}
                                onClick={() => toggleBan(user.id, user.is_banned)}
                                disabled={processing === user.id}
                              >
                                {processing === user.id ? (
                                  <Loader2 className="h-4 w-4 animate-spin" />
                                ) : user.is_banned ? (
                                  <>
                                    <Shield className="h-4 w-4 mr-1 ml-1" />
                                    {dir === 'rtl' ? 'إلغاء الحظر' : 'Unban'}
                                  </>
                                ) : (
                                  <>
                                    <ShieldOff className="h-4 w-4 mr-1 ml-1" />
                                    {dir === 'rtl' ? 'حظر المستخدم' : 'Ban User'}
                                  </>
                                )}
                              </Button>
                            )}
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Messages Tab */}
          <TabsContent value="messages">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <MessageSquare className="h-5 w-5" />
                  {t('contactMessages')}
                  {unreadMessagesCount > 0 && (
                    <Badge variant="destructive">
                      {unreadMessagesCount} {t('unreadMessages')}
                    </Badge>
                  )}
                </CardTitle>
              </CardHeader>
              <CardContent>
                {messages.length === 0 ? (
                  <div className="text-center py-12 text-muted-foreground">
                    {dir === 'rtl' ? 'لا توجد رسائل' : 'No messages'}
                  </div>
                ) : (
                  <div className="space-y-4">
                    {messages.map((message) => (
                      <Card
                        key={message.id}
                        className={`glass-card ${!message.is_read ? 'border-primary' : ''}`}
                      >
                        <CardContent className="p-4">
                          <div className="flex items-start justify-between gap-4">
                            <div className="flex-1">
                              <div className="flex items-center gap-2 mb-2">
                                <h4 className="font-semibold text-foreground">{message.name}</h4>
                                {!message.is_read && (
                                  <Badge variant="default" className="text-xs">
                                    {dir === 'rtl' ? 'جديد' : 'New'}
                                  </Badge>
                                )}
                              </div>
                              <p className="text-sm text-muted-foreground mb-2">{message.email}</p>
                              <p className="text-foreground">{message.message}</p>
                              <p className="text-xs text-muted-foreground mt-2">
                                {new Date(message.created_at).toLocaleDateString(language === 'ar' ? 'ar-SA' : 'en-US')}
                              </p>
                            </div>
                            {!message.is_read && (
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => markMessageRead(message.id)}
                              >
                                <Eye className="h-4 w-4 mr-1" />
                                {t('markAsRead')}
                              </Button>
                            )}
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          {/* Broadcast Tab */}
          <TabsContent value="broadcast">
            <Card>
              <CardHeader>
                <CardTitle>{t('broadcastRequest')}</CardTitle>
                <CardDescription>
                  {dir === 'rtl'
                    ? 'إرسال طلب بث لجميع المتبرعين أو تصفية حسب المدينة وفصيلة الدم'
                    : 'Send a broadcast request to all donors or filter by city and blood type'}
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="broadcast-message">
                    {dir === 'rtl' ? 'رسالة البث' : 'Broadcast Message'}
                  </Label>
                  <Textarea
                    id="broadcast-message"
                    placeholder={dir === 'rtl' ? 'اكتب رسالتك هنا...' : 'Type your message here...'}
                    value={broadcastMessage}
                    onChange={(e) => setBroadcastMessage(e.target.value)}
                    className="min-h-[120px] resize-none"
                  />
                </div>

                <div className="flex flex-col space-y-4">
                  <Label>{dir === 'rtl' ? 'تصفية المستهدفين (اختياري)' : 'Target Filters (Optional)'}</Label>
                  <div className="flex flex-col sm:flex-row gap-4">
                    <Select value={broadcastCity} onValueChange={setBroadcastCity}>
                      <SelectTrigger className="w-full sm:w-[200px]">
                        <MapPin className="h-4 w-4 mr-2 text-muted-foreground" />
                        <SelectValue placeholder={dir === 'rtl' ? 'جميع المدن' : 'All Cities'} />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">{dir === 'rtl' ? 'جميع المدن' : 'All Cities'}</SelectItem>
                        {cities.map((city) => (
                          <SelectItem key={city.id} value={city.id}>
                            {language === 'ar' ? city.name_ar : city.name_en}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>

                    <Select value={broadcastBloodType} onValueChange={setBroadcastBloodType}>
                      <SelectTrigger className="w-full sm:w-[200px]">
                        <Droplets className="h-4 w-4 mr-2 text-muted-foreground" />
                        <SelectValue placeholder={t('allBloodTypes')} />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">{t('allBloodTypes')}</SelectItem>
                        {BLOOD_TYPES.map((type) => (
                          <SelectItem key={type} value={type}>{type}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>

                    <Button
                      onClick={sendBroadcast}
                      disabled={sendingBroadcast || !broadcastMessage.trim()}
                      className="bg-primary hover:bg-primary/90 shadow-md"
                    >
                      {sendingBroadcast ? (
                        <Loader2 className="h-4 w-4 animate-spin mr-2" />
                      ) : (
                        <Send className="h-4 w-4 mr-2" />
                      )}
                      {t('sendBroadcast')}
                    </Button>
                  </div>
                  <p className="text-xs text-muted-foreground">
                    {dir === 'rtl'
                      ? '* إذا لم يتم اختيار فلاتر، سيتم إرسال الرسالة إلى جميع المستخدمين النشطين.'
                      : '* If no filters are selected, the message will be sent to ALL active users.'}
                  </p>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </Layout>
  );
};

export default AdminDashboard;
