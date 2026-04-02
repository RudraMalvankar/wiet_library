import React from 'react';
import { ActivityIndicator, View } from 'react-native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../context/AuthContext';
import { colors } from '../theme';
import { LoginScreen } from '../screens/LoginScreen';
import { ForgotPasswordScreen } from '../screens/ForgotPasswordScreen';
import { VerifyOtpScreen } from '../screens/VerifyOtpScreen';
import { DashboardScreen } from '../screens/DashboardScreen';
import { BooksScreen } from '../screens/BooksScreen';
import { SearchScreen } from '../screens/SearchScreen';
import { NotificationsScreen } from '../screens/NotificationsScreen';
import { ProfileScreen } from '../screens/ProfileScreen';
import { HistoryScreen } from '../screens/HistoryScreen';
import { RecommendationsScreen } from '../screens/RecommendationsScreen';
import { EventsScreen } from '../screens/EventsScreen';
import { DigitalIdScreen } from '../screens/DigitalIdScreen';
import { FootfallScreen } from '../screens/FootfallScreen';
import { EResourcesScreen } from '../screens/EResourcesScreen';

const AuthStack = createNativeStackNavigator();
const RootStack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

function Tabs() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: '#6b7280',
        tabBarStyle: { borderTopColor: colors.accent, borderTopWidth: 1 },
        tabBarIcon: ({ color, size }) => {
          const map: Record<string, keyof typeof Ionicons.glyphMap> = {
            Dashboard: 'home',
            Books: 'book',
            Search: 'search',
            Notifications: 'notifications',
            Profile: 'person',
          };
          return <Ionicons name={map[route.name] || 'ellipse'} size={size} color={color} />;
        },
      })}
    >
      <Tab.Screen name="Dashboard" component={DashboardScreen} />
      <Tab.Screen name="Books" component={BooksScreen} />
      <Tab.Screen name="Search" component={SearchScreen} />
      <Tab.Screen name="Notifications" component={NotificationsScreen} />
      <Tab.Screen name="Profile" component={ProfileScreen} />
    </Tab.Navigator>
  );
}

function AuthRoutes() {
  return (
    <AuthStack.Navigator>
      <AuthStack.Screen name="Login" component={LoginScreen} options={{ headerShown: false }} />
      <AuthStack.Screen name="ForgotPassword" component={ForgotPasswordScreen} options={{ title: 'Forgot Password' }} />
      <AuthStack.Screen name="VerifyOtp" component={VerifyOtpScreen} options={{ title: 'Verify OTP' }} />
    </AuthStack.Navigator>
  );
}

function AppRoutes() {
  return (
    <RootStack.Navigator>
      <RootStack.Screen name="HomeTabs" component={Tabs} options={{ headerShown: false }} />
      <RootStack.Screen name="History" component={HistoryScreen} options={{ title: 'Borrowing History' }} />
      <RootStack.Screen name="Recommendations" component={RecommendationsScreen} options={{ title: 'Recommendations' }} />
      <RootStack.Screen name="Events" component={EventsScreen} options={{ title: 'Library Events' }} />
      <RootStack.Screen name="DigitalId" component={DigitalIdScreen} options={{ title: 'Digital ID' }} />
      <RootStack.Screen name="Footfall" component={FootfallScreen} options={{ title: 'Footfall' }} />
      <RootStack.Screen name="EResources" component={EResourcesScreen} options={{ title: 'E-Resources' }} />
    </RootStack.Navigator>
  );
}

export function AppNavigator() {
  const { loading, token } = useAuth();

  if (loading) {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.primary }}>
        <ActivityIndicator size="large" color={colors.accent} />
      </View>
    );
  }

  return token ? <AppRoutes /> : <AuthRoutes />;
}
